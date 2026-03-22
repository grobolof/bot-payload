<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper\Model\Attachment;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class AttachmentMessage
{
    public function __construct(
        private int $date,
        #[SerializedName('from_id')]
        private int $fromId,
        private int $id,
        private int $version,
        private int $out,
        #[SerializedName('fwd_messages')]
        private array $fwdMessages,
        private bool $important,
        #[SerializedName('is_hidden')]
        private bool $isHidden,
        private array $attachments,
        #[SerializedName('conversation_message_id')]
        private int $conversationMessageId,
        private string $text,
        #[SerializedName('peer_id')]
        private int $peerId,
        #[SerializedName('random_id')]
        private int $randomId,
        private ?string $payload = null,
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

    public function getVersion(): int
    {
        return $this->version;
    }

    public function getOut(): int
    {
        return $this->out;
    }

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

    public function getPayload(): ?array
    {
        if (!empty($this->payload)) {
            return json_decode(json: $this->payload, associative: true);
        }

        return null;
    }
}
