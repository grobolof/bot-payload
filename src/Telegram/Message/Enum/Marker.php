<?php

declare(strict_types=1);

namespace BotPayload\Telegram\Message\Enum;

enum Marker: string
{
    case MARKER_LINEFEED = '#__MARKER_LINEFEED__#';
    case MARKER_FONT_BOLD_OPEN = '#__MARKER_FONT_BOLD_OPEN__#';
    case MARKER_FONT_BOLD_CLOSE = '#__MARKER_FONT_BOLD_CLOSE__#';
    case MARKER_URL_TEXT_OPEN = '#__MARKER_URL_TEXT_OPEN__#';
    case MARKER_URL_TEXT_CLOSE = '#__MARKER_URL_TEXT_CLOSE__#';
    case MARKER_URL_LINK_OPEN = '#__MARKER_URL_LINK_OPEN__#';
    case MARKER_URL_LINK_CLOSE = '#__MARKER_URL_LINK_CLOSE__#';
    case MARKER_LOWERCASE_CODE_OPEN = '#__MARKER_LOWERCASE_CODE_OPEN__#';
    case MARKER_LOWERCASE_CODE_CLOSE = '#__MARKER_LOWERCASE_CODE_CLOSE__#';

    public static function toArray(): array
    {
        return array_map(
            fn(self $marker) => $marker->value,
            self::cases()
        );
    }
}
