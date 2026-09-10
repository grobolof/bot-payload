<?php

declare(strict_types=1);

namespace BotMapperFormatter\Telegram\Mapper;

use BotMapperFormatter\AbstractBot;
use BotMapperFormatter\Telegram\Mapper\Model\TelegramUpdate;

class TelegramMapper extends AbstractBot
{
    public static function exec(array|string $data): TelegramUpdate
    {
        return self::hydrate(data: self::decode($data), type: TelegramUpdate::class);
    }
}
