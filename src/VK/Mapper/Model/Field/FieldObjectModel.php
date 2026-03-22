<?php

declare(strict_types=1);

namespace BotMapperFormatter\VK\Mapper\Model\Field;

use Symfony\Component\Serializer\Attribute\SerializedName;

readonly class FieldObjectModel
{
    public function __construct(
        private FieldClientInfoModel $clientInfo,
        private FieldMessageModel $message,
    ) {
    }

    public function getClientInfo(): FieldClientInfoModel
    {
        return $this->clientInfo;
    }

    public function getMessage(): FieldMessageModel
    {
        return $this->message;
    }
}
