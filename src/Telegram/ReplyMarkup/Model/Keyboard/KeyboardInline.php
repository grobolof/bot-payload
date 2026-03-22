<?php

declare(strict_types=1);

namespace BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard;

use BotMapperFormatter\Telegram\ReplyMarkup\Contract\KeyboardInterface;
use BotMapperFormatter\Telegram\ReplyMarkup\Exception\PositiveIntException;

/**
 * Клавиатура в сообщении
 *
 * @property-read int|null $buttonsPerRow кол-во кнопок в ряду (имеет приоритет над рядами кнопок; опционально)
 */
readonly class KeyboardInline implements KeyboardInterface
{
    public function __construct(
        private ?int $buttonsPerRow = null,
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
}
