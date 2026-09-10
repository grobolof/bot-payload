<?php

declare(strict_types=1);

namespace BotPayload\VK\Formatter\Model;

readonly class ReplyMarkup
{
    /**
     * @param list<Button> $buttons
     */
    public function __construct(
        private Keyboard $keyboard,
        private array $buttons = [],
    ) {
    }

    public function getKeyboard(): Keyboard
    {
        return $this->keyboard;
    }

    /**
     * @return list<Button>
     */
    public function getButtons(): array
    {
        return $this->buttons;
    }
}
