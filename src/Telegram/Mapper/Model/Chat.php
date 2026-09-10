<?php

declare(strict_types=1);

namespace BotMapperFormatter\Telegram\Mapper\Model;

readonly class Chat
{
    public function __construct(
        private int $id,
        private string $type,
        private ?string $title = null,
        private ?string $username = null,
        private ?string $firstName = null,
        private ?string $lastName = null,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function isPrivate(): bool
    {
        return $this->type === 'private';
    }
}
