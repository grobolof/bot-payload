<?php

declare(strict_types=1);

namespace BotMapperFormatter;

use BotMapperFormatter\Message\Enum\Mod;
use BotMapperFormatter\Message\Mod\MarkdownV2;
use BotMapperFormatter\ReplyMarkup\KeyboardHandle;
use BotMapperFormatter\ReplyMarkup\Model\Keyboard\KeyboardInline;
use BotMapperFormatter\ReplyMarkup\Model\Keyboard\Keyboard;
use BotMapperFormatter\ReplyMarkup\Model\Keyboard\KeyboardRemove;
use BotMapperFormatter\ReplyMarkup\Model\ReplyMarkup;

readonly class Formatter
{
    public static function message(Mod $mod, string $text): string
    {
        return match ($mod) {
            Mod::MARKDOWN_V2 => MarkdownV2::handle(text: $text)
        };
    }

    public static function replayMarkup(ReplyMarkup $model): array
    {
        $keyboard = $model->getKeyboard();

        return match ($keyboard::class) {
            KeyboardInline::class => KeyboardHandle::keyboardInline(
                replyMarkupModel: $model,
                keyboard: $keyboard
            ),
            Keyboard::class => KeyboardHandle::keyboard(
                replyMarkupModel: $model,
                keyboard: $keyboard
            ),
            KeyboardRemove::class => KeyboardHandle::keyboardRemove(
                keyboard: $keyboard
            ),
        };
    }
}
