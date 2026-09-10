#!/usr/bin/env python3
"""Build a short GitHub Release title and a detailed body from the previous tag."""

from __future__ import annotations

import argparse
import json
import os
import re
import subprocess
import sys
from pathlib import Path

MARKER = "<!-- auto-release-notes -->"

# More specific prefixes first. Tuple: (path prefix, label, group).
CATEGORIES: list[tuple[str, str, str]] = [
    ("src/Telegram/Mapper", "гидратация Telegram", "Telegram"),
    ("src/Telegram/Message", "Markdown Telegram", "Telegram"),
    ("src/Telegram/ReplyMarkup", "клавиатуры Telegram", "Telegram"),
    ("src/Telegram/TelegramFormatter.php", "отправка Telegram", "Telegram"),
    ("src/VK/Mapper", "гидратация VK", "VK"),
    ("src/VK/Formatter", "клавиатуры и отправка VK", "VK"),
    ("src/Exception", "ошибки", "core"),
    ("src/AbstractBot.php", "общее API", "core"),
    ("tests/", "тесты", "tests"),
    ("phpunit.xml.dist", "тесты", "tests"),
    ("README.md", "документация", "docs"),
    ("composer.json", "зависимости Composer", "deps"),
    (".github/", "CI/CD", "ci"),
]

EMPTY_TREE = "4b825dc642cb6eb9a060e54bf8d69288fbee4904"
SKIP_FILE_NAMES = {".gitkeep"}

GENERIC_SUBJECTS = {
    "app",
    "update",
    "обновление",
    "обноаление",
    "wip",
    "fix",
    "changes",
}


def run(args: list[str], check: bool = True) -> str:
    result = subprocess.run(args, check=check, capture_output=True, text=True)
    return result.stdout.strip()


def previous_tag(current: str) -> str | None:
    raw = run(["git", "tag", "--list", "--merged", current, "--sort=-version:refname"])
    for tag in raw.splitlines():
        tag = tag.strip()
        if tag and tag != current:
            return tag
    return None


def git_range(prev: str | None, current: str) -> str:
    if prev:
        return f"{prev}..{current}"
    return current


def changed_files(prev: str | None, current: str) -> list[str]:
    if prev:
        raw = run(["git", "diff", "--name-only", prev, current])
    else:
        raw = run(["git", "ls-tree", "-r", "--name-only", current])
    files = []
    for line in raw.splitlines():
        path = line.strip()
        if not path or Path(path).name in SKIP_FILE_NAMES:
            continue
        files.append(path)
    return files


def diff_stat(prev: str | None, current: str) -> str:
    base = prev or EMPTY_TREE
    return run(["git", "diff", "--stat", base, current])


def commits(git_range_spec: str) -> list[tuple[str, str]]:
    raw = run(
        [
            "git",
            "log",
            git_range_spec,
            "--pretty=format:%h%x09%s",
            "--no-merges",
        ]
    )
    rows: list[tuple[str, str]] = []
    for line in raw.splitlines():
        if "\t" not in line:
            continue
        sha, subject = line.split("\t", 1)
        rows.append((sha, subject.strip()))
    return rows


def matched_categories(files: list[str]) -> list[tuple[str, str]]:
    found: list[tuple[str, str]] = []
    seen: set[str] = set()
    for path in files:
        for prefix, label, group in CATEGORIES:
            if path == prefix or path.startswith(prefix):
                if label not in seen:
                    found.append((label, group))
                    seen.add(label)
                break
    return found


def join_ru(parts: list[str]) -> str:
    if not parts:
        return ""
    if len(parts) == 1:
        return parts[0]
    if len(parts) == 2:
        return f"{parts[0]} и {parts[1]}"
    return f"{', '.join(parts[:-1])} и {parts[-1]}"


def collapse_group(labels: list[str], group: str) -> str:
    if len(labels) == 1:
        return labels[0]
    has_mapper = any("гидратация" in label for label in labels)
    has_formatter = any(
        word in label for label in labels for word in ("Markdown", "клавиатур", "отправка")
    )
    if has_mapper and has_formatter:
        return f"гидратация и форматирование {group}"
    return group


def short_summary(files: list[str], commit_rows: list[tuple[str, str]]) -> str:
    matched = matched_categories(files)
    by_group: dict[str, list[str]] = {}
    for label, group in matched:
        by_group.setdefault(group, []).append(label)

    platforms = [group for group in ("Telegram", "VK") if group in by_group]
    if len(platforms) >= 2:
        src_parts = platforms
    else:
        src_parts = [
            collapse_group(by_group[group], group)
            for group in platforms
        ]
        if not src_parts and "core" in by_group:
            src_parts = [collapse_group(by_group["core"], "общее API")]

    extras: list[str] = []
    if "tests" in by_group:
        extras.append("тесты")
    if not src_parts:
        if "deps" in by_group:
            extras.append("зависимости Composer")
        if "docs" in by_group:
            extras.append("документация")
        if "ci" in by_group:
            extras.append("CI")

    summary = join_ru(src_parts + extras)
    if summary:
        return summary

    subjects = [
        subject
        for _, subject in commit_rows
        if subject.strip().lower() not in GENERIC_SUBJECTS
        and not subject.lower().startswith("merge ")
    ]
    if subjects:
        return subjects[0][:72]

    return "изменения с прошлого релиза"


def group_files(files: list[str]) -> list[tuple[str, list[str]]]:
    grouped: dict[str, list[str]] = {}
    order: list[str] = []
    other: list[str] = []
    for path in files:
        matched = False
        for prefix, label, _group in CATEGORIES:
            if path == prefix or path.startswith(prefix):
                if label not in grouped:
                    grouped[label] = []
                    order.append(label)
                if path not in grouped[label]:
                    grouped[label].append(path)
                matched = True
                break
        if not matched:
            other.append(path)
    sections = [(label, grouped[label]) for label in order]
    if other:
        sections.append(("прочее", other))
    return sections


def github_generated_notes(tag: str, prev: str | None) -> str:
    repo = os.environ.get("GITHUB_REPOSITORY")
    if not repo or not os.environ.get("GITHUB_TOKEN"):
        return ""
    payload = {"tag_name": tag}
    if prev:
        payload["previous_tag_name"] = prev
    result = subprocess.run(
        [
            "gh",
            "api",
            f"repos/{repo}/releases/generate-notes",
            "--method",
            "POST",
            "--input",
            "-",
        ],
        input=json.dumps(payload),
        capture_output=True,
        text=True,
    )
    if result.returncode != 0:
        return ""
    try:
        body = json.loads(result.stdout).get("body") or ""
    except json.JSONDecodeError:
        return ""
    return body.strip()


def is_prerelease(tag: str) -> bool:
    return bool(re.search(r"(?i)(alpha|beta|rc|dev|snapshot)", tag))


def build_body(
    tag: str,
    prev: str | None,
    files: list[str],
    commit_rows: list[tuple[str, str]],
    stat: str,
    generated: str,
    repo: str,
) -> str:
    server = os.environ.get("GITHUB_SERVER_URL", "https://github.com")
    compare = (
        f"{server}/{repo}/compare/{prev}...{tag}"
        if prev
        else f"{server}/{repo}/commits/{tag}"
    )
    lines: list[str] = [
        MARKER,
        "",
        f"## Что изменилось в `{tag}`",
        "",
    ]
    if prev:
        lines.append(f"Сравнение с предыдущим релизом [`{prev}`]({server}/{repo}/releases/tag/{prev}).")
    else:
        lines.append("Первый релиз пакета.")
    lines.append("")

    sections = group_files(files)
    if sections:
        lines.append("### По областям")
        lines.append("")
        for label, paths in sections:
            lines.append(f"- **{label}**")
            for path in paths[:20]:
                lines.append(f"  - `{path}`")
            if len(paths) > 20:
                lines.append(f"  - … и ещё {len(paths) - 20} файлов")
        lines.append("")

    if commit_rows:
        lines.append("### Коммиты")
        lines.append("")
        for sha, subject in commit_rows:
            url = f"{server}/{repo}/commit/{sha}"
            lines.append(f"- [`{sha}`]({url}) {subject}")
        lines.append("")

    if generated:
        lines.append("### Pull requests")
        lines.append("")
        lines.append(generated)
        lines.append("")

    if stat:
        lines.extend(["### Статистика diff", "", "```", stat, "```", ""])

    lines.extend(["### Полный changelog", "", compare, ""])
    return "\n".join(lines).rstrip() + "\n"


def existing_release(tag: str) -> tuple[str, str] | None:
    result = subprocess.run(
        ["gh", "release", "view", tag, "--json", "name,body"],
        capture_output=True,
        text=True,
    )
    if result.returncode != 0:
        return None
    data = json.loads(result.stdout)
    return data.get("name") or "", data.get("body") or ""


def should_overwrite(body: str, force: bool) -> bool:
    if "<!-- skip-auto-release-notes -->" in body and not force:
        return False
    return True


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser()
    parser.add_argument("--tag", required=True)
    parser.add_argument("--previous", default=None, help="Override previous tag/ref")
    parser.add_argument("--repo", default=os.environ.get("GITHUB_REPOSITORY", ""))
    parser.add_argument("--out-dir", default=".github/release-output")
    parser.add_argument("--apply", action="store_true", help="Create or update the GitHub Release")
    parser.add_argument("--force", action="store_true")
    return parser.parse_args()


def main() -> int:
    args = parse_args()
    tag = args.tag
    repo = args.repo
    if not repo:
        print("GITHUB_REPOSITORY / --repo is required", file=sys.stderr)
        return 1

    prev = args.previous if args.previous is not None else previous_tag(tag)
    files = changed_files(prev, tag)
    commit_rows = commits(git_range(prev, tag))
    summary = short_summary(files, commit_rows)
    title = f"{tag} — {summary}"
    generated = github_generated_notes(tag, prev)
    body = build_body(
        tag=tag,
        prev=prev,
        files=files,
        commit_rows=commit_rows,
        stat=diff_stat(prev, tag),
        generated=generated,
        repo=repo,
    )

    out_dir = Path(args.out_dir)
    out_dir.mkdir(parents=True, exist_ok=True)
    (out_dir / "title.txt").write_text(title + "\n", encoding="utf-8")
    (out_dir / "body.md").write_text(body, encoding="utf-8")
    (out_dir / "previous-tag.txt").write_text((prev or "") + "\n", encoding="utf-8")

    print(f"title: {title}")
    print(f"previous: {prev or '(none)'}")
    print(f"files: {len(files)}")
    print(f"commits: {len(commit_rows)}")

    if not args.apply:
        return 0

    current = existing_release(tag)
    prerelease_flag = ["--prerelease"] if is_prerelease(tag) else []
    if current is None:
        cmd = [
            "gh",
            "release",
            "create",
            tag,
            "--title",
            title,
            "--notes-file",
            str(out_dir / "body.md"),
            "--verify-tag",
            *prerelease_flag,
        ]
        subprocess.run(cmd, check=True)
        print(f"created GitHub Release {tag}")
        return 0

    _current_title, current_body = current
    if not should_overwrite(current_body, args.force):
        print("release already has custom notes; skipping overwrite")
        return 0

    cmd = [
        "gh",
        "release",
        "edit",
        tag,
        "--title",
        title,
        "--notes-file",
        str(out_dir / "body.md"),
    ]
    subprocess.run(cmd, check=True)
    print(f"updated GitHub Release {tag}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
