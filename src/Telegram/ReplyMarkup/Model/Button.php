<?php

declare(strict_types=1);

namespace BotMapperFormatter\Telegram\ReplyMarkup\Model;

use BotMapperFormatter\Telegram\ReplyMarkup\Exception\PositiveIntException;

/**
 * Модель кнопки Telegram клавиатуры
 *
 * @property-read string $name Текст кнопки (обязательный)
 * @property-read int $row Номер строки для кнопки (обязательный)
 * @property-read string|null $callbackData Данные для callback функции (опционально)
 * @property-read string|null $switchInlineQuery Запрос для inline-режима (опционально)
 * @property-read string|null $url URL для кнопки-ссылки (опционально)
 */
readonly class Button
{
    public function __construct(
        private string $name,
        private int $row = 1,
        private ?string $callbackData = null,
        private ?string $switchInlineQuery = null,
        private ?string $url = null
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
}
