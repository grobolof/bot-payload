<?php

declare(strict_types=1);

namespace BotPayload\Telegram\ReplyMarkup\Model\Keyboard;

use BotPayload\Telegram\ReplyMarkup\Contract\KeyboardInterface;

/**
 * Удалить reply-клавиатуру (ReplyKeyboardRemove).
 */
readonly class KeyboardRemove implements KeyboardInterface
{
    public function __construct(
        private bool $isKeyboardRemove = true,
        private bool $selective = false,
    ) {
    }

    public function getIsKeyboardRemove(): bool
    {
        return $this->isKeyboardRemove;
    }

    public function isSelective(): bool
    {
        return $this->selective;
    }
}
