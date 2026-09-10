<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper\Model\Attachment;

readonly class AttachmentClientInfo
{
    /**
     * @param list<string> $buttonActions
     */
    public function __construct(
        private bool $keyboard,
        private bool $inlineKeyboard,
        private bool $carousel,
        private int $langId,
        private array $buttonActions = [],
    ) {
    }

    public function getKeyboard(): bool
    {
        return $this->keyboard;
    }

    public function getInlineKeyboard(): bool
    {
        return $this->inlineKeyboard;
    }

    public function getCarousel(): bool
    {
        return $this->carousel;
    }

    public function getLangId(): int
    {
        return $this->langId;
    }

    /**
     * @return list<string>
     */
    public function getButtonActions(): array
    {
        return $this->buttonActions;
    }
}
