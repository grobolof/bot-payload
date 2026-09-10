# Релизы `bot-payload`

Публичная Composer-библиотека: версию выбираем **руками**, GitHub Actions заполняет title/description и пингует Packagist.

Авторелиз на каждый merge в `main` **не делаем**. Merge копит изменения, тег — когда пакет готов уехать на [packagist.org/packages/grobolof/bot-payload](https://packagist.org/packages/grobolof/bot-payload).

## Как выпустить

Любой способ. Title и описание в форме GitHub можно оставить пустыми — workflow сам их напишет (~30 секунд).

1. Тег:

   ```bash
   git tag 1.1.0
   git push origin 1.1.0
   ```

2. GitHub → Releases → Draft a new release → новый тег → Publish.

3. Actions → **Release** → Run workflow — если тег уже есть, notes перезапишутся.

Формат тега: `MAJOR.MINOR.PATCH` (`1.1.0`, не `1.1`). Префикс `v` не обязателен. Не пишите поле `version` в `composer.json`.

Чтобы **не** перезаписывать свои notes, в описание релиза вставьте:

```html
<!-- skip-auto-release-notes -->
```

## Какую цифру ставить

Смотрим на публичный PHP API (`TelegramMapper::exec`, `TelegramFormatter::sendMessage`, модели), не на размер diff.

| Версия | Когда | Пример |
| --- | --- | --- |
| **Patch** `1.0.x` | API не менялся | баг в гидратации, тест, README, CI |
| **Minor** `1.x.0` | добавили, старый код жив | новый метод, новое поле Update, новый тип кнопки |
| **Major** `x.0.0` | старый код может упасть | удалили/переименовали метод, сменили сигнатуру или namespace, подняли PHP `^8.2` → `^8.3` |

Новые файлы mapper/formatter без ломания старых вызовов — это **minor**. Несколько PR без тега — нормально; релиз, когда хотите, чтобы Packagist это увидел.

Потребители живут на `^1.0`: `composer update` подтянет patch и minor, major — нет, пока сами не напишут `^2.0`.

## Что делает Actions

| Workflow | Когда | Что |
| --- | --- | --- |
| [workflows/ci.yml](workflows/ci.yml) | push/PR в `main` | `composer validate` + PHPUnit на PHP 8.2 / 8.3 / 8.4 |
| [workflows/release.yml](workflows/release.yml) | тег, GitHub Release, или Run workflow | короткий title по изменённым путям, подробный body с прошлого тега, ping Packagist |

Title строится по каталогам (`src/Telegram/Mapper`, `src/VK/Formatter`, `tests/…`), а не по сообщениям коммитов. Body: области, файлы, коммиты, PR, `git diff --stat`, ссылка compare. Скрипт: [scripts/generate-release-notes.py](scripts/generate-release-notes.py). Категории для кнопки Generate release notes в UI: [release.yml](release.yml).

## Packagist

Если вход на packagist.org через GitHub, webhook часто уже стоит (на странице пакета будет «This package is auto-updated»). Workflow всё равно дергает API, чтобы не ждать недельный crawl.

Секреты репозитория (Settings → Secrets and variables → Actions):

| Secret | Значение |
| --- | --- |
| `PACKAGIST_USERNAME` | `grobolof` |
| `PACKAGIST_TOKEN` | **SAFE**-токен с [packagist.org/profile](https://packagist.org/profile/) |

Без секретов job не падает: пишет предупреждение и выходит.

## Почему не авторелиз с `main`

Робот не читает PHP. Он умеет только то, что ему сказали: conventional commits (`feat` / `fix` / `feat!`), лейблы PR или ручной bump.

Сейчас коммиты вроде «Обновление» не отличить major от patch. Если резать тег с каждого merge:

- README начнёт плодить `1.0.1`, `1.0.2`;
- случайный breaking change, помеченный как minor, сломает ботов на `composer update`.

Пока оставляем: PR → `main` (CI) часто; релиз — редко, руками. Позже можно добавить Release PR (release-please) на conventional commits, не ломая ручной тег.
