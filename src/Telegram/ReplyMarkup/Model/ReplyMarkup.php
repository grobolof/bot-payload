<?php

declare(strict_types=1);

namespace BotMapperFormatter\Telegram\ReplyMarkup\Model;

use BotMapperFormatter\Telegram\ReplyMarkup\Contract\KeyboardInterface;

/**
 * Модель Telegram-клавиатуры.
 *
 * @param list<Button> $buttons
 */
readonly class ReplyMarkup
{
    /**
     * @param list<Button> $buttons
     */
    public function __construct(
        private KeyboardInterface $keyboard,
        private array $buttons = [],
    ) {
    }

    /**
     * @return list<Button>
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function getKeyboard(): KeyboardInterface
    {
        return $this->keyboard;
    }
}
