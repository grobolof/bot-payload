<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Formatter\Model;

use BotMapperFormatter\Exception\PositiveIntException;

readonly class Keyboard
{
    public function __construct(
        private bool $oneTime = false,
        private bool $inline = false,
        private ?int $buttonsPerRow = null,
    ) {
        if (is_int($buttonsPerRow) && $buttonsPerRow < 1) {
            throw new PositiveIntException(
                number: $buttonsPerRow,
                message: 'Количество кнопок в ряду должно быть больше 0. Ваше количество кнопок в ряду: %d'
            );
        }
    }

    public function isOneTime(): bool
    {
        return $this->oneTime;
    }

    public function isInline(): bool
    {
        return $this->inline;
    }

    public function getButtonsPerRow(): ?int
    {
        return $this->buttonsPerRow;
    }
}
