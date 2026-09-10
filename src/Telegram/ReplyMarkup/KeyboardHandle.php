<?php

declare(strict_types=1);

namespace BotMapperFormatter\Telegram\ReplyMarkup;

use BotMapperFormatter\Telegram\ReplyMarkup\Model\Button;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\Keyboard;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\KeyboardInline;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\KeyboardRemove;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\ReplyMarkup;

class KeyboardHandle
{
    /**
     * @return array<string, mixed>
     */
    public static function keyboard(
        ReplyMarkup $replyMarkupModel,
        Keyboard $keyboard
    ): array {
        $payload = [
            'keyboard' => self::rows(
                replyMarkupModel: $replyMarkupModel,
                buttonsPerRow: $keyboard->getButtonsPerRow(),
                mapButton: self::replyKeyboardButton(...)
            ),
            'resize_keyboard' => $keyboard->getResizeKeyboard(),
            'one_time_keyboard' => $keyboard->getOneTimeKeyboard(),
        ];

        if ($keyboard->isPersistent()) {
            $payload['is_persistent'] = true;
        }

        if ($keyboard->isSelective()) {
            $payload['selective'] = true;
        }

        if ($keyboard->getInputFieldPlaceholder() !== null) {
            $payload['input_field_placeholder'] = $keyboard->getInputFieldPlaceholder();
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public static function keyboardInline(
        ReplyMarkup $replyMarkupModel,
        KeyboardInline $keyboard
    ): array {
        return [
            'inline_keyboard' => self::rows(
                replyMarkupModel: $replyMarkupModel,
                buttonsPerRow: $keyboard->getButtonsPerRow(),
                mapButton: self::inlineKeyboardButton(...)
            ),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function keyboardRemove(KeyboardRemove $keyboard): array
    {
        $payload = [
            'remove_keyboard' => $keyboard->getIsKeyboardRemove(),
        ];

        if ($keyboard->isSelective()) {
            $payload['selective'] = true;
        }

        return $payload;
    }

    /**
     * @return array<string, string|bool>
     */
    private static function replyKeyboardButton(Button $button): array
    {
        $payload = [
            'text' => $button->getName(),
        ];

        if ($button->getRequestContact()) {
            $payload['request_contact'] = true;
        }

        if ($button->getRequestLocation()) {
            $payload['request_location'] = true;
        }

        return $payload;
    }

    /**
     * @return array<string, string>
     */
    private static function inlineKeyboardButton(Button $button): array
    {
        $payload = [
            'text' => $button->getName(),
        ];

        $switchInlineQuery = $button->getSwitchInlineQuery();
        $url = $button->getUrl();
        $callbackData = $button->getCallbackData();

        if ($switchInlineQuery !== null) {
            $payload['switch_inline_query'] = $switchInlineQuery;
        } elseif ($url !== null && $url !== '') {
            $payload['url'] = $url;
        } elseif ($callbackData !== null && $callbackData !== '') {
            $payload['callback_data'] = $callbackData;
        } else {
            throw new \InvalidArgumentException(
                'Inline-кнопка должна содержать callback_data, url или switch_inline_query.'
            );
        }

        return $payload;
    }

    /**
     * @param callable(Button): array<string, mixed> $mapButton
     * @return list<list<array<string, mixed>>>
     */
    private static function rows(
        ReplyMarkup $replyMarkupModel,
        ?int $buttonsPerRow,
        callable $mapButton,
    ): array {
        if (is_int($buttonsPerRow)) {
            $buttons = [];

            foreach ($replyMarkupModel->getButtons() as $button) {
                $buttons[] = $mapButton($button);
            }

            return array_chunk($buttons, $buttonsPerRow);
        }

        $lines = [];

        foreach ($replyMarkupModel->getButtons() as $button) {
            $lines[$button->getRow()][] = $mapButton($button);
        }

        ksort($lines);

        return array_values($lines);
    }
}
