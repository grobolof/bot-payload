<?php

declare(strict_types=1);

namespace BotMapperFormatter\Message\Mod;

use BotMapperFormatter\Message\Enum\Marker;
use BotMapperFormatter\Message\Exception\TagsMismatchException;

readonly class MarkdownV2
{
    private const CHARACTERS = '.,?_!~()[]-=*"|+';

    public static function handle(string $text): string
    {
        self::checkOpenCloseTags(text: $text);

        $markers = Marker::toArray();

        // Убираем 2 и более пробелов между текстом
        $text = preg_replace('/\s+/u', ' ', $text);

        $tmpMarkers = [];

        foreach ($markers as $index => $marker) {
            $tmpKey = '$$$REPLACEMENT' . $index . '$$$';
            $tmpMarkers[$tmpKey] = $marker;
            $text = str_replace($marker, $tmpKey, $text);
        }

        $text = addcslashes(string: $text, characters: self::CHARACTERS);

        foreach ($tmpMarkers as $tmpKey => $marker) {
            $text = str_replace($tmpKey, $marker, $text);
        }

        foreach ($markers as $marker) {
            $text = str_replace(
                search: $marker,
                replace: self::markerToSymbol(marker: Marker::from(value: $marker)),
                subject: $text
            );
        }

        // Убираем все пробелы после символов "\n" (разрыв строки)
        return preg_replace(pattern: '/(\n+)[ \t]+/', replacement: '$1', subject: $text);
    }

    private static function markerToSymbol(Marker $marker): string
    {
        return match ($marker) {
            Marker::MARKER_LINEFEED => "\n",
            Marker::MARKER_FONT_BOLD_OPEN, Marker::MARKER_FONT_BOLD_CLOSE => "*",
            Marker::MARKER_URL_TEXT_OPEN => "[",
            Marker::MARKER_URL_TEXT_CLOSE => "]",
            Marker::MARKER_URL_LINK_OPEN => "(",
            Marker::MARKER_URL_LINK_CLOSE => ")",
            Marker::MARKER_LOWERCASE_CODE_OPEN, Marker::MARKER_LOWERCASE_CODE_CLOSE => "`",
        };
    }

    private static function checkOpenCloseTags(string $text): void
    {
        $markers = [
            [
                Marker::MARKER_FONT_BOLD_OPEN->value,
                Marker::MARKER_FONT_BOLD_CLOSE->value
            ],
            [
                Marker::MARKER_URL_TEXT_OPEN->value,
                Marker::MARKER_URL_TEXT_CLOSE->value
            ],
            [
                Marker::MARKER_URL_LINK_OPEN->value,
                Marker::MARKER_URL_LINK_CLOSE->value
            ],
            [
                Marker::MARKER_LOWERCASE_CODE_OPEN->value,
                Marker::MARKER_LOWERCASE_CODE_CLOSE->value
            ]
        ];

        foreach ($markers as $marker) {
            $markerOpenCount = substr_count(haystack: $text, needle: $marker[0]);
            $markerCloseCount = substr_count(haystack: $text, needle: $marker[1]);

            if ($markerOpenCount !== $markerCloseCount) {
                throw new TagsMismatchException(
                    markerOpen: $marker[0],
                    markerClose: $marker[1],
                    markerOpenCount: $markerOpenCount,
                    markerCloseCount: $markerCloseCount
                );
            }
        }
    }
}
