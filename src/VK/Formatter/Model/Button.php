<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Formatter\Model;

use BotMapperFormatter\Exception\PositiveIntException;
use BotMapperFormatter\VK\Formatter\Enum\ButtonColor;
use BotMapperFormatter\VK\Formatter\Enum\ButtonType;

readonly class Button
{
    /**
     * @param array<string, mixed>|string|null $payload
     */
    public function __construct(
        private string $label,
        private int $row = 1,
        private ButtonType $type = ButtonType::TEXT,
        private ButtonColor $color = ButtonColor::SECONDARY,
        private array|string|null $payload = null,
        private ?string $link = null,
        private ?int $appId = null,
        private ?int $ownerId = null,
        private ?string $hash = null,
    ) {
        if ($row < 1) {
            throw new PositiveIntException(
                number: $row,
                message: 'Номер строки должен быть больше 0. Ваш номер строки: %d'
            );
        }
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRow(): int
    {
        return $this->row;
    }

    public function getType(): ButtonType
    {
        return $this->type;
    }

    public function getColor(): ButtonColor
    {
        return $this->color;
    }

    /**
     * @return array<string, mixed>|string|null
     */
    public function getPayload(): array|string|null
    {
        return $this->payload;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getAppId(): ?int
    {
        return $this->appId;
    }

    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }
}
