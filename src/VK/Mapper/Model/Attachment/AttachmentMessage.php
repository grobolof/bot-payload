<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper\Model\Attachment;

readonly class AttachmentMessage
{
    /**
     * @param list<array<string, mixed>> $fwdMessages
     * @param list<array<string, mixed>> $attachments
     * @param array<string, mixed>|null $replyMessage
     * @param array<string, mixed>|null $geo
     * @param array<string, mixed>|null $action
     * @param array<string, mixed>|null $keyboard
     */
    public function __construct(
        private int $date,
        private int $fromId,
        private int $id,
        private int $peerId,
        private int $out = 0,
        private array $fwdMessages = [],
        private bool $important = false,
        private bool $isHidden = false,
        private array $attachments = [],
        private int $conversationMessageId = 0,
        private string $text = '',
        private int $randomId = 0,
        private ?int $version = null,
        private ?string $payload = null,
        private ?array $replyMessage = null,
        private ?array $geo = null,
        private ?array $action = null,
        private ?array $keyboard = null,
        private ?string $ref = null,
        private ?string $refSource = null,
    ) {
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function getFromId(): int
    {
        return $this->fromId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function getOut(): int
    {
        return $this->out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getFwdMessages(): array
    {
        return $this->fwdMessages;
    }

    public function getImportant(): bool
    {
        return $this->important;
    }

    public function getIsHidden(): bool
    {
        return $this->isHidden;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getConversationMessageId(): int
    {
        return $this->conversationMessageId;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getPeerId(): int
    {
        return $this->peerId;
    }

    public function getRandomId(): int
    {
        return $this->randomId;
    }

    public function getPayload(): mixed
    {
        if ($this->payload === null || $this->payload === '') {
            return null;
        }

        try {
            return json_decode($this->payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return $this->payload;
        }
    }

    public function getRawPayload(): ?string
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getReplyMessage(): ?array
    {
        return $this->replyMessage;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getGeo(): ?array
    {
        return $this->geo;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getAction(): ?array
    {
        return $this->action;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getKeyboard(): ?array
    {
        return $this->keyboard;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function getRefSource(): ?string
    {
        return $this->refSource;
    }
}
