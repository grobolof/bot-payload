<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper;

use BotMapperFormatter\AbstractBot;
use BotMapperFormatter\VK\Mapper\Contract\VKMapperInterface;
use BotMapperFormatter\VK\Mapper\Enum\VKMapperMessageTypeEnum;
use BotMapperFormatter\VK\Mapper\Model\MessageNew;

readonly class VKMapper extends AbstractBot
{
    public static function exec(array|string $data): ?VKMapperInterface
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return match (VKMapperMessageTypeEnum::tryFrom($data['type'] ?? '')) {
            VKMapperMessageTypeEnum::MESSAGE_NEW => self::messageNew(data: $data),
            default => null,
        };
    }

    private static function messageNew(array $data): MessageNew
    {
        // Форматировать json: self::serializer()->deserialize($data, MessageNew::class, 'json');
        return self::serializer()->denormalize(data: $data, type: MessageNew::class);
    }
}
