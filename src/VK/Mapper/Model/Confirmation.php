<?php

declare(strict_types=1);

namespace BotPayload\VK\Mapper\Model;

use BotPayload\VK\Mapper\Contract\VKMapperInterface;

readonly class Confirmation implements VKMapperInterface
{
    public function __construct(
        private int $groupId,
        private string $type = 'confirmation',
        private ?string $secret = null,
    ) {
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }
}
