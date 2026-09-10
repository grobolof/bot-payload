<?php

declare(strict_types=1);

namespace BotPayload\VK\Mapper\Enum;

enum VKMapperMessageTypeEnum: string
{
    case CONFIRMATION = 'confirmation';
    case MESSAGE_NEW = 'message_new';
    case MESSAGE_EVENT = 'message_event';
}
