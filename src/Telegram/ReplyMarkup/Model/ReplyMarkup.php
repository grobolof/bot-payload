<?php

declare(strict_types=1);

namespace BotPayload\Telegram\ReplyMarkup\Model;

use BotPayload\Telegram\ReplyMarkup\Contract\KeyboardInterface;

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
