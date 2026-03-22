<?php

declare(strict_types=1);

namespace BotMapperFormatter\ReplyMarkup\Model\Keyboard;

use BotMapperFormatter\ReplyMarkup\Contract\KeyboardInterface;
use BotMapperFormatter\ReplyMarkup\Exception\PositiveIntException;

/**
 * Клавиатура внизу
 *
 * @property-read int|null $buttonsPerRow кол-во кнопок в ряду (имеет приоритет над рядами кнопок; опционально)
 * @property-read bool $resizeKeyboard масштабировать клавиатуру (опционально)
 * @property-read bool $oneTimeKeyboard клавиатуру "сворачивается" в нижнее меню (опционально)
 */
readonly class Keyboard implements KeyboardInterface
{
    public function __construct(
        private ?int $buttonsPerRow = null,
        private bool $resizeKeyboard = true,
        private bool $oneTimeKeyboard = true,
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
}
