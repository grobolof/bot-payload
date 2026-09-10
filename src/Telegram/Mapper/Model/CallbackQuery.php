<?php

declare(strict_types=1);

namespace BotPayload\Telegram\Mapper\Model;

readonly class CallbackQuery
{
    public function __construct(
        private string $id,
        private User $from,
        private string $chatInstance,
        private ?Message $message = null,
        private ?string $inlineMessageId = null,
        private ?string $data = null,
        private ?string $gameShortName = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFrom(): User
    {
        return $this->from;
    }

    public function getChatInstance(): string
    {
        return $this->chatInstance;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function getInlineMessageId(): ?string
    {
        return $this->inlineMessageId;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getGameShortName(): ?string
    {
        return $this->gameShortName;
    }
}
