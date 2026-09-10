<?php

declare(strict_types=1);

namespace BotPayload\VK\Mapper\Model;

readonly class MessageEventObject
{
    public function __construct(
        private int $userId,
        private int $peerId,
        private string $eventId,
        private mixed $payload = null,
        private ?int $conversationMessageId = null,
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getPeerId(): int
    {
        return $this->peerId;
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getPayload(): mixed
    {
        return $this->payload;
    }

    public function getConversationMessageId(): ?int
    {
        return $this->conversationMessageId;
    }
}
