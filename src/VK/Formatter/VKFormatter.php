<?php

declare(strict_types=1);

namespace BotPayload\VK\Formatter;

use BotPayload\VK\Formatter\Enum\ButtonType;
use BotPayload\VK\Formatter\Model\Button;
use BotPayload\VK\Formatter\Model\ReplyMarkup;

class VKFormatter
{
    /**
     * Parameters for messages.send. keyboard is already a JSON string, as VK API expects.
     *
     * @return array<string, mixed>
     */
    public static function sendMessage(
        int $peerId,
        string $text,
        ?ReplyMarkup $replyMarkup = null,
        ?int $randomId = null,
        ?string $attachment = null,
        ?int $replyTo = null,
    ): array {
        $payload = [
            'peer_id' => $peerId,
            'message' => $text,
            'random_id' => $randomId ?? random_int(0, PHP_INT_MAX),
        ];

        if ($replyMarkup !== null) {
            $payload['keyboard'] = self::keyboardJson(model: $replyMarkup);
        }

        if ($attachment !== null) {
            $payload['attachment'] = $attachment;
        }

        if ($replyTo !== null) {
            $payload['reply_to'] = $replyTo;
        }

        return $payload;
    }

    /**
     * Parameters for messages.sendMessageEventAnswer.
     *
     * @param array<string, mixed>|null $eventData
     * @return array<string, mixed>
     */
    public static function eventAnswer(
        string $eventId,
        int $userId,
        int $peerId,
        ?array $eventData = null,
    ): array {
        $payload = [
            'event_id' => $eventId,
            'user_id' => $userId,
            'peer_id' => $peerId,
        ];

        if ($eventData !== null) {
            $payload['event_data'] = json_encode($eventData, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        return $payload;
    }

    /**
     * @return array{type: string, text: string}
     */
    public static function snackbar(string $text): array
    {
        return [
            'type' => 'show_snackbar',
            'text' => $text,
        ];
    }

    /**
     * @return array{type: string, link: string}
     */
    public static function openLink(string $link): array
    {
        return [
            'type' => 'open_link',
            'link' => $link,
        ];
    }

    /**
     * Keyboard JSON for messages.send `keyboard` parameter.
     */
    public static function keyboardJson(ReplyMarkup $model): string
    {
        return json_encode(self::keyboard(model: $model), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * @return array{one_time: bool, inline: bool, buttons: list<list<array<string, mixed>>>}
     */
    public static function keyboard(ReplyMarkup $model): array
    {
        return [
            'one_time' => $model->getKeyboard()->isOneTime(),
            'inline' => $model->getKeyboard()->isInline(),
            'buttons' => self::rows(model: $model),
        ];
    }

    /**
     * Empty keyboard that hides the current reply keyboard.
     *
     * @return array{one_time: bool, inline: bool, buttons: array<never>}
     */
    public static function keyboardRemove(): array
    {
        return [
            'one_time' => true,
            'inline' => false,
            'buttons' => [],
        ];
    }

    /**
     * @return list<list<array<string, mixed>>>
     */
    private static function rows(ReplyMarkup $model): array
    {
        $buttonsPerRow = $model->getKeyboard()->getButtonsPerRow();

        if (is_int($buttonsPerRow)) {
            $buttons = [];

            foreach ($model->getButtons() as $button) {
                $buttons[] = self::button(button: $button);
            }

            return array_chunk($buttons, $buttonsPerRow);
        }

        $lines = [];

        foreach ($model->getButtons() as $button) {
            $lines[$button->getRow()][] = self::button(button: $button);
        }

        ksort($lines);

        return array_values($lines);
    }

    /**
     * @return array<string, mixed>
     */
    private static function button(Button $button): array
    {
        $action = [
            'type' => $button->getType()->value,
        ];

        if ($button->getType() !== ButtonType::LOCATION) {
            $action['label'] = $button->getLabel();
        }

        $payload = $button->getPayload();
        if ($payload !== null) {
            $action['payload'] = is_string($payload)
                ? $payload
                : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }

        if ($button->getType() === ButtonType::OPEN_LINK) {
            if ($button->getLink() === null) {
                throw new \InvalidArgumentException('Кнопка open_link должна содержать link.');
            }

            $action['link'] = $button->getLink();
        }

        if ($button->getType() === ButtonType::OPEN_APP) {
            if ($button->getAppId() === null) {
                throw new \InvalidArgumentException('Кнопка open_app должна содержать app_id.');
            }

            $action['app_id'] = $button->getAppId();

            if ($button->getOwnerId() !== null) {
                $action['owner_id'] = $button->getOwnerId();
            }

            if ($button->getHash() !== null) {
                $action['hash'] = $button->getHash();
            }
        }

        $item = [
            'action' => $action,
        ];

        if (in_array($button->getType(), [ButtonType::TEXT, ButtonType::CALLBACK], true)) {
            $item['color'] = $button->getColor()->value;
        }

        return $item;
    }
}
