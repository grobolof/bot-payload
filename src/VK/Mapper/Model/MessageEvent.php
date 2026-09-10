<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper\Model;

use BotMapperFormatter\VK\Mapper\Contract\VKMapperInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class MessageEvent implements VKMapperInterface
{
    public function __construct(
        private int $groupId,
        private string $type,
        private MessageEventObject $object,
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

    public function getObject(): MessageEventObject
    {
        return $this->object;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function getUserId(): int
    {
        return $this->object->getUserId();
    }

    public function getPeerId(): int
    {
        return $this->object->getPeerId();
    }

    public function getCallbackEventId(): string
    {
        return $this->object->getEventId();
    }

    public function getPayload(): mixed
    {
        return $this->object->getPayload();
    }
}
