<?php

declare(strict_types=1);

namespace BotPayload\VK\Mapper\Model;

use BotPayload\VK\Mapper\Contract\VKMapperInterface;
use BotPayload\VK\Mapper\Model\Attachment\AttachmentObject;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class MessageNew implements VKMapperInterface
{
    public function __construct(
        private int $groupId,
        private string $type,
        private AttachmentObject $object,
        private ?string $eventId = null,
        #[SerializedName('v')]
        private ?string $version = null,
        private ?string $secret = null,
    ) {
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEventId(): ?string
    {
        return $this->eventId;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getObject(): AttachmentObject
    {
        return $this->object;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function getText(): string
    {
        return $this->object->getMessage()->getText();
    }

    public function getPeerId(): int
    {
        return $this->object->getMessage()->getPeerId();
    }

    public function getFromId(): int
    {
        return $this->object->getMessage()->getFromId();
    }

    public function getPayload(): mixed
    {
        return $this->object->getMessage()->getPayload();
    }
}
