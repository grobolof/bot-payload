<?php

declare(strict_types=1);

namespace BotPayload\Telegram\ReplyMarkup\Model;

use BotPayload\Telegram\ReplyMarkup\Exception\PositiveIntException;

/**
 * Кнопка Telegram-клавиатуры.
 *
 * Reply-клавиатура использует text / requestContact / requestLocation.
 * Inline-клавиатура использует callbackData / url / switchInlineQuery.
 */
readonly class Button
{
    public function __construct(
        private string $name,
        private int $row = 1,
        private ?string $callbackData = null,
        private ?string $switchInlineQuery = null,
        private ?string $url = null,
        private bool $requestContact = false,
        private bool $requestLocation = false,
    ) {
        if ($row < 1) {
            throw new PositiveIntException(
                number: $row,
                message: 'Номер строки должен быть больше 0. Ваш номер строки: %d'
            );
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRow(): int
    {
        return $this->row;
    }

    public function getCallbackData(): ?string
    {
        return $this->callbackData;
    }

    public function getSwitchInlineQuery(): ?string
    {
        return $this->switchInlineQuery;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getRequestContact(): bool
    {
        return $this->requestContact;
    }

    public function getRequestLocation(): bool
    {
        return $this->requestLocation;
    }
}
