<?php

declare(strict_types=1);

namespace BotPayload\Exception;

class PositiveIntException extends \InvalidArgumentException
{
    public function __construct(int $number, ?string $message = null)
    {
        $message = sprintf(
            $message ?? 'Число должно быть больше 0. Ваше число: %d',
            $number
        );

        parent::__construct(message: $message, code: 400);
    }
}
