<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper;

use BotMapperFormatter\AbstractBot;
use BotMapperFormatter\VK\Mapper\Contract\VKMapperInterface;
use BotMapperFormatter\VK\Mapper\Enum\VKMapperMessageTypeEnum;
use BotMapperFormatter\VK\Mapper\Model\Confirmation;
use BotMapperFormatter\VK\Mapper\Model\MessageEvent;
use BotMapperFormatter\VK\Mapper\Model\MessageNew;

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
