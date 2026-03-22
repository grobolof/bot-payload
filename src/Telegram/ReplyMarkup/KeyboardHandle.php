<?php

declare(strict_types=1);

namespace BotMapperFormatter\Telegram\ReplyMarkup;

use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\Keyboard;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\KeyboardInline;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\KeyboardRemove;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\ReplyMarkup;

readonly class KeyboardHandle
{
    public static function keyboard(
        ReplyMarkup $replyMarkupModel,
        Keyboard $keyboard
    ): array {
        $lines = [];

        if (is_int($keyboard->getButtonsPerRow())) {
            $buttons = [];

            foreach ($replyMarkupModel->getButtons() as $button) {
                $buttons[] = [
                    'text' => $button->getName(),
                    'callback_data' => $button->getCallbackData(),
                ];
            }

            $lines = self::distributeToLines(buttons: $buttons, buttonsPerRow: $keyboard->getButtonsPerRow());
        } else {
            foreach ($replyMarkupModel->getButtons() as $button) {
                $lines[$button->getRow()][] = [
                    'text' => $button->getName(),
                    'callback_data' => $button->getCallbackData(),
                ];
            }
        }

        return [
            'keyboard' => array_values($lines),
            'resize_keyboard' => $keyboard->getResizeKeyboard(),
            'one_time_keyboard' => $keyboard->getOneTimeKeyboard(),
        ];
    }

    public static function keyboardInline(
        ReplyMarkup $replyMarkupModel,
        KeyboardInline $keyboard
    ): array {
        $lines = [];

        if (is_int($keyboard->getButtonsPerRow())) {
            $buttons = [];

            foreach ($replyMarkupModel->getButtons() as $button) {
                if (!empty($button->getSwitchInlineQuery())) {
                    $buttons[] = [
                        'text' => $button->getName(),
                        'switch_inline_query' => $button->getSwitchInlineQuery(),
                    ];
                } elseif (!empty($button->getUrl())) {
                    $buttons[] = [
                        'text' => $button->getName(),
                        'url' => $button->getUrl(),
                    ];
                } else {
                    $buttons[] = [
                        'text' => $button->getName(),
                        'callback_data' => $button->getCallbackData(),
                    ];
                }
            }

            $lines = self::distributeToLines(buttons: $buttons, buttonsPerRow: $keyboard->getButtonsPerRow());
        } else {
            foreach ($replyMarkupModel->getButtons() as $button) {
                if (!empty($button->getSwitchInlineQuery())) {
                    $lines[$button->getRow()][] = [
                        'text' => $button->getName(),
                        'switch_inline_query' => $button->getSwitchInlineQuery(),
                    ];
                } elseif (!empty($button->getUrl())) {
                    $lines[$button->getRow()][] = [
                        'text' => $button->getName(),
                        'url' => $button->getUrl(),
                    ];
                } else {
                    $lines[$button->getRow()][] = [
                        'text' => $button->getName(),
                        'callback_data' => $button->getCallbackData(),
                    ];
                }
            }
        }

        return ['inline_keyboard' => array_values($lines)];
    }

    public static function keyboardRemove(KeyboardRemove $keyboard): array
    {
        return ['remove_keyboard' => $keyboard->getIsKeyboardRemove()];
    }

    private static function distributeToLines(array $buttons, int $buttonsPerRow): array
    {
        return array_chunk($buttons, $buttonsPerRow);
    }
}
