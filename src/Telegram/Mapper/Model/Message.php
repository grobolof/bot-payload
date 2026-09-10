<?php

declare(strict_types=1);

namespace BotPayload\Telegram\Mapper\Model;

readonly class Message
{
    /**
     * @param array<int, array<string, mixed>> $entities
     * @param array<int, array<string, mixed>>|null $photo
     * @param array<string, mixed>|null $document
     * @param array<string, mixed>|null $voice
     * @param array<string, mixed>|null $audio
     * @param array<string, mixed>|null $video
     * @param array<string, mixed>|null $sticker
     * @param array<string, mixed>|null $location
     * @param array<string, mixed>|null $webAppData
     */
    public function __construct(
        private int $messageId,
        private int $date,
        private Chat $chat,
        private ?User $from = null,
        private ?string $text = null,
        private ?string $caption = null,
        private array $entities = [],
        private ?Contact $contact = null,
        private ?int $messageThreadId = null,
        private ?array $photo = null,
        private ?array $document = null,
        private ?array $voice = null,
        private ?array $audio = null,
        private ?array $video = null,
        private ?array $sticker = null,
        private ?array $location = null,
        private ?array $webAppData = null,
    ) {
    }

    public function getMessageId(): int
    {
        return $this->messageId;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function getChat(): Chat
    {
        return $this->chat;
    }

    public function getFrom(): ?User
    {
        return $this->from;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function getMessageThreadId(): ?int
    {
        return $this->messageThreadId;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    public function getPhoto(): ?array
    {
        return $this->photo;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getDocument(): ?array
    {
        return $this->document;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getVoice(): ?array
    {
        return $this->voice;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getAudio(): ?array
    {
        return $this->audio;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getVideo(): ?array
    {
        return $this->video;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSticker(): ?array
    {
        return $this->sticker;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLocation(): ?array
    {
        return $this->location;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getWebAppData(): ?array
    {
        return $this->webAppData;
    }

    public function getBotCommand(): ?string
    {
        $text = $this->text;
        if ($text === null || !str_starts_with($text, '/')) {
            return null;
        }

        if (preg_match('/^\/([a-zA-Z0-9_]+)/', $text, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }
}
