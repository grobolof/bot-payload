<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper\Model;

use BotMapperFormatter\VK\Mapper\Contract\VKMapperInterface;
use BotMapperFormatter\VK\Mapper\Model\Attachment\AttachmentObject;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class NewMessage implements VKMapperInterface
{
    public function __construct(
        #[SerializedName('group_id')]
        private int $groupId,
        private string $type,
        #[SerializedName('event_id')]
        private string $eventId,
        #[SerializedName('v')]
        private string $version,
        private AttachmentObject $object,
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

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getVersion(): string
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
}
