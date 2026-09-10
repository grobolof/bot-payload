<?php

declare(strict_types=1);

namespace BotPayload\VK\Mapper\Contract;

interface VKMapperInterface
{
    public function getType(): string;

    public function getGroupId(): int;
}
