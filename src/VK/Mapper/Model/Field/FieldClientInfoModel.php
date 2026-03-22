<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper\Model\Field;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class FieldClientInfoModel
{
    public function __construct(
        private bool $keyboard,
        #[SerializedName('inline_keyboard')]
        private bool $inlineKeyboard,
        private bool $carousel,
        #[SerializedName('lang_id')]
        private int $langId,
        #[SerializedName('button_actions')]
        private array $buttonActions,
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

    public function getButtonActions(): array
    {
        return $this->buttonActions;
    }
}
