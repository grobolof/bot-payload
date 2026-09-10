<?php

declare(strict_types=1);

namespace BotPayload\Telegram\ReplyMarkup\Model\Keyboard;

use BotPayload\Telegram\ReplyMarkup\Contract\KeyboardInterface;
use BotPayload\Telegram\ReplyMarkup\Exception\PositiveIntException;

/**
 * Reply-клавиатура внизу экрана (ReplyKeyboardMarkup).
 */
readonly class Keyboard implements KeyboardInterface
{
    public function __construct(
        private ?int $buttonsPerRow = null,
        private bool $resizeKeyboard = true,
        private bool $oneTimeKeyboard = true,
        private bool $isPersistent = false,
        private bool $selective = false,
        private ?string $inputFieldPlaceholder = null,
    ) {
        if (is_int($buttonsPerRow) && $buttonsPerRow < 1) {
            throw new PositiveIntException(
                number: $buttonsPerRow,
                message: 'Количество кнопок в ряду должно быть больше 0. Ваше количество кнопок в ряду: %d'
            );
        }
    }

    public function getButtonsPerRow(): ?int
    {
        return $this->buttonsPerRow;
    }

    public function getResizeKeyboard(): bool
    {
        return $this->resizeKeyboard;
    }

    public function getOneTimeKeyboard(): bool
    {
        return $this->oneTimeKeyboard;
    }

    public function isPersistent(): bool
    {
        return $this->isPersistent;
    }

    public function isSelective(): bool
    {
        return $this->selective;
    }

    public function getInputFieldPlaceholder(): ?string
    {
        return $this->inputFieldPlaceholder;
    }
}
