<?php

declare(strict_types=1);

namespace BotMapperFormatter\Telegram\Mapper\Model;

readonly class Contact
{
    public function __construct(
        private string $phoneNumber,
        private string $firstName,
        private ?string $lastName = null,
        private ?int $userId = null,
        private ?string $vcard = null,
    ) {
    }

    public function getPhoneNumber(): string
    {
        return $this->phoneNumber;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getVcard(): ?string
    {
        return $this->vcard;
    }
}
