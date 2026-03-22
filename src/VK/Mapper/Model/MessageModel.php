<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper\Model;

use BotMapperFormatter\VK\Mapper\Contract\VKMapperInterface;
use BotMapperFormatter\VK\Mapper\Model\Field\FieldObjectModel;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class MessageModel implements VKMapperInterface
{
    public function __construct(
        #[SerializedName('group_id')]
        private int $groupId,
        private string $type,
        #[SerializedName('event_id')]
        private string $eventId,
        #[SerializedName('v')]
        private string $version,
        private FieldObjectModel $object,
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

    public function getObject(): FieldObjectModel
    {
        return $this->object;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }
}
