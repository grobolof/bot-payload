<?php

declare(strict_types=1);

namespace BotPayload\Telegram;

use BotPayload\Telegram\Exception\TelegramApiException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class TelegramApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $botToken,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function call(string $method, array $payload): void
    {
        if ('' === \trim($this->botToken)) {
            throw new TelegramApiException('Telegram bot token is not configured.');
        }

        $response = $this->httpClient->request(
            method: 'POST',
            url: \sprintf('https://api.telegram.org/bot%s/%s', $this->botToken, $method),
            options: ['json' => $payload],
        );

        $statusCode = $response->getStatusCode();
        try {
            /** @var array{ok?: bool, description?: string} $body */
            $body = $response->toArray(false);
        } catch (\Throwable $exception) {
            throw new TelegramApiException(
                message: \sprintf('Telegram API %s returned a non-JSON response (HTTP %d).', $method, $statusCode),
                previous: $exception,
            );
        }

        if ($statusCode >= 400 || true !== ($body['ok'] ?? false)) {
            $description = \is_string($body['description'] ?? null)
                ? $body['description']
                : 'Unknown Telegram API error';

            throw new TelegramApiException(
                message: \sprintf('Telegram API %s failed: %s', $method, $description),
            );
        }
    }
}
