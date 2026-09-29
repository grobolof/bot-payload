<?php

declare(strict_types=1);

namespace BotPayload\Telegram;

use BotPayload\Telegram\Message\Enum\Mod;
use BotPayload\Telegram\Message\Mod\MarkdownV2;
use BotPayload\Telegram\ReplyMarkup\KeyboardHandle;
use BotPayload\Telegram\ReplyMarkup\Model\Keyboard\Keyboard;
use BotPayload\Telegram\ReplyMarkup\Model\Keyboard\KeyboardInline;
use BotPayload\Telegram\ReplyMarkup\Model\Keyboard\KeyboardRemove;
use BotPayload\Telegram\ReplyMarkup\Model\ReplyMarkup;

class TelegramFormatter
{
    public static function message(Mod $mod, string $text): string
    {
        return match ($mod) {
            Mod::MARKDOWN_V2 => MarkdownV2::handle(text: $text),
        };
    }

    /**
     * Payload for sendMessage. reply_markup is an object: send the whole array as JSON body,
     * or json_encode only reply_markup if you use application/x-www-form-urlencoded.
     *
     * @return array<string, mixed>
     */
    public static function sendMessage(
        int|string $chatId,
        string $text,
        ?Mod $parseMode = null,
        ?ReplyMarkup $replyMarkup = null,
        bool $disableNotification = false,
        ?int $replyToMessageId = null,
        ?int $messageThreadId = null,
    ): array {
        $payload = [
            'chat_id' => $chatId,
            'text' => null !== $parseMode ? self::message(mod: $parseMode, text: $text) : $text,
        ];

        if (null !== $parseMode) {
            $payload['parse_mode'] = $parseMode->value;
        }

        if (null !== $replyMarkup) {
            $payload['reply_markup'] = self::replyMarkup(model: $replyMarkup);
        }

        if ($disableNotification) {
            $payload['disable_notification'] = true;
        }

        if (null !== $replyToMessageId) {
            $payload['reply_to_message_id'] = $replyToMessageId;
        }

        if (null !== $messageThreadId) {
            $payload['message_thread_id'] = $messageThreadId;
        }

        return $payload;
    }

    /**
     * Payload for sendPhoto.
     *
     * @return array<string, mixed>
     */
    public static function sendPhoto(
        int|string $chatId,
        string $photo,
        ?string $caption = null,
        ?Mod $parseMode = null,
    ): array {
        $payload = [
            'chat_id' => $chatId,
            'photo' => $photo,
        ];

        if (null !== $caption && '' !== $caption) {
            $payload['caption'] = null !== $parseMode
                ? self::message(mod: $parseMode, text: $caption)
                : $caption;
        }

        if (null !== $parseMode) {
            $payload['parse_mode'] = $parseMode->value;
        }

        return $payload;
    }

    /**
     * Payload for sendMediaGroup.
     *
     * @param list<array<string, mixed>> $media
     *
     * @return array<string, mixed>
     */
    public static function sendMediaGroup(int|string $chatId, array $media): array
    {
        return [
            'chat_id' => $chatId,
            'media' => $media,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function inputMediaPhoto(string $photo, ?string $caption = null, ?Mod $parseMode = null): array
    {
        $item = [
            'type' => 'photo',
            'media' => $photo,
        ];

        if (null !== $caption && '' !== $caption) {
            $item['caption'] = null !== $parseMode
                ? self::message(mod: $parseMode, text: $caption)
                : $caption;
        }

        if (null !== $parseMode) {
            $item['parse_mode'] = $parseMode->value;
        }

        return $item;
    }

    /**
     * Payload for answerCallbackQuery. Call this after every inline button click.
     *
     * @return array<string, mixed>
     */
    public static function answerCallbackQuery(
        string $callbackQueryId,
        ?string $text = null,
        bool $showAlert = false,
        ?string $url = null,
        ?int $cacheTime = null,
    ): array {
        $payload = [
            'callback_query_id' => $callbackQueryId,
        ];

        if (null !== $text) {
            $payload['text'] = $text;
        }

        if ($showAlert) {
            $payload['show_alert'] = true;
        }

        if (null !== $url) {
            $payload['url'] = $url;
        }

        if (null !== $cacheTime) {
            $payload['cache_time'] = $cacheTime;
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    public static function replyMarkup(ReplyMarkup $model): array
    {
        $keyboard = $model->getKeyboard();

        return match ($keyboard::class) {
            KeyboardInline::class => KeyboardHandle::keyboardInline(
                replyMarkupModel: $model,
                keyboard: $keyboard,
            ),
            Keyboard::class => KeyboardHandle::keyboard(
                replyMarkupModel: $model,
                keyboard: $keyboard,
            ),
            KeyboardRemove::class => KeyboardHandle::keyboardRemove(
                keyboard: $keyboard,
            ),
        };
    }

    /**
     * @deprecated Use replyMarkup()
     *
     * @return array<string, mixed>
     */
    public static function replayMarkup(ReplyMarkup $model): array
    {
        return self::replyMarkup(model: $model);
    }
}
