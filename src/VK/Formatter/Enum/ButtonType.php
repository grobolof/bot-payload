<?php

declare(strict_types=1);

namespace BotPayload\VK\Formatter\Enum;

enum ButtonType: string
{
    case TEXT = 'text';
    case CALLBACK = 'callback';
    case OPEN_LINK = 'open_link';
    case OPEN_APP = 'open_app';
    case LOCATION = 'location';
    case VKPAY = 'vkpay';
}
