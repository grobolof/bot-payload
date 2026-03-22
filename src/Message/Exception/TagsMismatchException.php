<?php

declare(strict_types=1);

namespace BotMapperFormatter\Message\Exception;

class TagsMismatchException extends \InvalidArgumentException
{
    public function __construct(
        string $markerOpen,
        string $markerClose,
        int $markerOpenCount,
        int $markerCloseCount,
    ) {
        // Определяем какой тег и сколько не хватает
        if ($markerOpenCount > $markerCloseCount) {
            $missingMarker = $markerClose;
            $missingCount = $markerOpenCount - $markerCloseCount;
        } else {
            $missingMarker = $markerOpen;
            $missingCount = $markerCloseCount - $markerOpenCount;
        }

        $message = sprintf(
            'Не хватает тега "%s" в количестве: %d. Открывающих: %d, закрывающих: %d',
            $missingMarker,
            $missingCount,
            $markerOpenCount,
            $markerCloseCount
        );

        parent::__construct(message: $message, code: 400);
    }
}
