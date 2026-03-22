<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper;

use BotMapperFormatter\AbstractBot;
use BotMapperFormatter\VK\Mapper\Contract\VKMapperInterface;
use BotMapperFormatter\VK\Mapper\Enum\VKMapperMessageTypeEnum;
use BotMapperFormatter\VK\Mapper\Model\MessageModel;

readonly class VKMapper extends AbstractBot
{
    public static function map(array|string $data): ?VKMapperInterface
    {
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return match (VKMapperMessageTypeEnum::tryFrom($data['type'] ?? '')) {
            VKMapperMessageTypeEnum::MESSAGE_NEW => self::messageNewType(data: $data),
            default => null,
        };
    }

    /**
     * @throws ExceptionInterface
     */
    private static function messageNewType(array $data): MessageModel
    {
//        return self::serializer()->deserialize($data, MessageModel::class, 'json');

        return self::serializer()->denormalize(data: $data, type: MessageModel::class);
    }
}
