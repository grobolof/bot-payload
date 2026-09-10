<?php

declare(strict_types=1);

namespace BotMapperFormatter\Telegram\Mapper\Model;

use BotMapperFormatter\Telegram\Mapper\Contract\TelegramMapperInterface;

readonly class TelegramUpdate implements TelegramMapperInterface
{
    public function __construct(
        private int $updateId,
        private ?Message $message = null,
        private ?Message $editedMessage = null,
        private ?CallbackQuery $callbackQuery = null,
    ) {
    }

    public function getUpdateId(): int
    {
        return $this->updateId;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function getEditedMessage(): ?Message
    {
        return $this->editedMessage;
    }

    public function getCallbackQuery(): ?CallbackQuery
    {
        return $this->callbackQuery;
    }

    public function isMessage(): bool
    {
        return $this->message !== null;
    }

    public function isEditedMessage(): bool
    {
        return $this->editedMessage !== null;
    }

    public function isCallbackQuery(): bool
    {
        return $this->callbackQuery !== null;
    }

    public function getChatId(): ?int
    {
        if ($this->message !== null) {
            return $this->message->getChat()->getId();
        }

        if ($this->editedMessage !== null) {
            return $this->editedMessage->getChat()->getId();
        }

        return $this->callbackQuery?->getMessage()?->getChat()->getId();
    }

    public function getFrom(): ?User
    {
        return $this->message?->getFrom()
            ?? $this->editedMessage?->getFrom()
            ?? $this->callbackQuery?->getFrom();
    }

    public function getText(): ?string
    {
        return $this->message?->getText()
            ?? $this->editedMessage?->getText();
    }

    public function getCallbackData(): ?string
    {
        return $this->callbackQuery?->getData();
    }
}
