<?php

declare(strict_types=1);

namespace BotPayload\Exception;

class InvalidPayloadException extends \InvalidArgumentException
{
    public function __construct(string $message, ?\Throwable $previous = null)
    {
        parent::__construct(message: $message, code: 400, previous: $previous);
    }
}
