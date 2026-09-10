<?php

declare(strict_types=1);

namespace BotPayload\Telegram\Mapper;

use BotPayload\AbstractBot;
use BotPayload\Telegram\Mapper\Model\TelegramUpdate;

class TelegramMapper extends AbstractBot
{
    public static function exec(array|string $data): TelegramUpdate
    {
        return self::hydrate(data: self::decode($data), type: TelegramUpdate::class);
    }
}
