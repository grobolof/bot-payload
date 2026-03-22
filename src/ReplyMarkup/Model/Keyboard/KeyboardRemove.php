<?php

declare(strict_types=1);

namespace BotMapperFormatter\ReplyMarkup\Model\Keyboard;

use BotMapperFormatter\ReplyMarkup\Contract\KeyboardInterface;

/**
 * Удалить клавиатуру
 *
 * @property-read bool $isKeyboardRemove флаг того надо ли удалить клавиатуру (опционально)
 */
readonly class KeyboardRemove implements KeyboardInterface
{
    public function __construct(
        private bool $isKeyboardRemove = false,
    ) {
    }

    public function getIsKeyboardRemove(): bool
    {
        return $this->isKeyboardRemove;
    }
}
