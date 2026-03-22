<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper\Model\Attachment;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class AttachmentObject
{
    public function __construct(
        private AttachmentClientInfo $clientInfo,
        private AttachmentMessage $message,
    ) {
    }

    public function getClientInfo(): AttachmentClientInfo
    {
        return $this->clientInfo;
    }

    public function getMessage(): AttachmentMessage
    {
        return $this->message;
    }
}
