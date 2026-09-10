<?php

declare(strict_types=1);

namespace BotPayload\Telegram\Mapper\Model;

readonly class User
{
    public function __construct(
        private int $id,
        private bool $isBot,
        private string $firstName,
        private ?string $lastName = null,
        private ?string $username = null,
        private ?string $languageCode = null,
        private bool $isPremium = false,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isBot(): bool
    {
        return $this->isBot;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getLanguageCode(): ?string
    {
        return $this->languageCode;
    }

    public function isPremium(): bool
    {
        return $this->isPremium;
    }
}
