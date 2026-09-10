<?php

declare(strict_types=1);

namespace BotPayload\VK\Formatter\Enum;

enum ButtonColor: string
{
    case PRIMARY = 'primary';
    case SECONDARY = 'secondary';
    case POSITIVE = 'positive';
    case NEGATIVE = 'negative';
}
