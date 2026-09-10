<?php

declare(strict_types=1);

namespace BotMapperFormatter\Telegram;

use BotMapperFormatter\Telegram\Message\Enum\Mod;
use BotMapperFormatter\Telegram\Message\Mod\MarkdownV2;
use BotMapperFormatter\Telegram\ReplyMarkup\KeyboardHandle;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\Keyboard;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\KeyboardInline;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\KeyboardRemove;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\ReplyMarkup;

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
            'text' => $parseMode !== null ? self::message(mod: $parseMode, text: $text) : $text,
        ];

        if ($parseMode !== null) {
            $payload['parse_mode'] = $parseMode->value;
        }

        if ($replyMarkup !== null) {
            $payload['reply_markup'] = self::replyMarkup(model: $replyMarkup);
        }

        if ($disableNotification) {
            $payload['disable_notification'] = true;
        }

        if ($replyToMessageId !== null) {
            $payload['reply_to_message_id'] = $replyToMessageId;
        }

        if ($messageThreadId !== null) {
            $payload['message_thread_id'] = $messageThreadId;
        }

        return $payload;
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

        if ($text !== null) {
            $payload['text'] = $text;
        }

        if ($showAlert) {
            $payload['show_alert'] = true;
        }

        if ($url !== null) {
            $payload['url'] = $url;
        }

        if ($cacheTime !== null) {
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

    /**
     * @deprecated Use replyMarkup()
     * @return array<string, mixed>
     */
    public static function replayMarkup(ReplyMarkup $model): array
    {
        return self::replyMarkup(model: $model);
    }
}
