<?php

declare(strict_types=1);

namespace BotPayload\VK\Mapper;

use BotPayload\AbstractBot;
use BotPayload\VK\Mapper\Contract\VKMapperInterface;
use BotPayload\VK\Mapper\Enum\VKMapperMessageTypeEnum;
use BotPayload\VK\Mapper\Model\Confirmation;
use BotPayload\VK\Mapper\Model\MessageEvent;
use BotPayload\VK\Mapper\Model\MessageNew;

class VKMapper extends AbstractBot
{
    public static function exec(array|string $data): ?VKMapperInterface
    {
        $data = self::decode($data);

        return match (VKMapperMessageTypeEnum::tryFrom($data['type'] ?? '')) {
            VKMapperMessageTypeEnum::MESSAGE_NEW => self::hydrate(data: $data, type: MessageNew::class),
            VKMapperMessageTypeEnum::MESSAGE_EVENT => self::hydrate(data: $data, type: MessageEvent::class),
            VKMapperMessageTypeEnum::CONFIRMATION => self::hydrate(data: $data, type: Confirmation::class),
            default => null,
        };
    }
}
