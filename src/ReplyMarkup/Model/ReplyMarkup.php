<?php

declare(strict_types=1);

namespace BotMapperFormatter\ReplyMarkup\Model;

use BotMapperFormatter\ReplyMarkup\Contract\KeyboardInterface;

/**
 * Модель Telegram клавиатуры
 *
 * @property-read array $buttons массив кнопок (обязательный)
 * @property-read KeyboardInterface|null $keyboard клавиатура (опционально)
 * @property-read bool $isKeyboardRemove удалить клавиатуру (опционально)
 */
readonly class ReplyMarkup
{
    public function __construct(
        private KeyboardInterface $keyboard,
        private array $buttons = [],
    ) {
    }

    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function getKeyboard(): ?KeyboardInterface
    {
        return $this->keyboard;
    }
}
