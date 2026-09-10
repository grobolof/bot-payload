<?php

declare(strict_types=1);

namespace BotPayload\VK\Mapper\Model\Attachment;

readonly class AttachmentObject
{
    public function __construct(
        private AttachmentMessage $message,
        private ?AttachmentClientInfo $clientInfo = null,
    ) {
    }

    public function getClientInfo(): ?AttachmentClientInfo
    {
        return $this->clientInfo;
    }

    public function getMessage(): AttachmentMessage
    {
        return $this->message;
    }
}
