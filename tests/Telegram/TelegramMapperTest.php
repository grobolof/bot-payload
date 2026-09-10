<?php

declare(strict_types=1);

namespace BotMapperFormatter\Tests\Telegram;

use BotMapperFormatter\Exception\InvalidPayloadException;
use BotMapperFormatter\Telegram\Mapper\Model\TelegramUpdate;
use BotMapperFormatter\Telegram\Mapper\TelegramMapper;
use PHPUnit\Framework\TestCase;

final class TelegramMapperTest extends TestCase
{
    public function testHydratesIncomingMessage(): void
    {
        $update = TelegramMapper::exec(data: [
            'update_id' => 123456,
            'message' => [
                'message_id' => 10,
                'date' => 1710000000,
                'text' => '/start payload',
                'from' => [
                    'id' => 111,
                    'is_bot' => false,
                    'first_name' => 'Ivan',
                    'last_name' => 'Petrov',
                    'username' => 'ivan',
                    'language_code' => 'ru',
                ],
                'chat' => [
                    'id' => 111,
                    'type' => 'private',
                    'first_name' => 'Ivan',
                    'username' => 'ivan',
                ],
                'entities' => [
                    [
                        'offset' => 0,
                        'length' => 6,
                        'type' => 'bot_command',
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(TelegramUpdate::class, $update);
        $this->assertTrue($update->isMessage());
        $this->assertFalse($update->isCallbackQuery());
        $this->assertSame(123456, $update->getUpdateId());
        $this->assertSame(111, $update->getChatId());
        $this->assertSame('/start payload', $update->getText());
        $this->assertSame('start', $update->getMessage()?->getBotCommand());
        $this->assertSame('ivan', $update->getFrom()?->getUsername());
        $this->assertSame('bot_command', $update->getMessage()?->getEntities()[0]['type']);
    }

    public function testHydratesCallbackQuery(): void
    {
        $update = TelegramMapper::exec(data: json_encode([
            'update_id' => 99,
            'callback_query' => [
                'id' => 'cb-1',
                'from' => [
                    'id' => 222,
                    'is_bot' => false,
                    'first_name' => 'Anna',
                ],
                'chat_instance' => 'chat-instance',
                'data' => 'menu:open',
                'message' => [
                    'message_id' => 5,
                    'date' => 1710000001,
                    'chat' => [
                        'id' => -100,
                        'type' => 'supergroup',
                        'title' => 'Group',
                    ],
                    'from' => [
                        'id' => 1,
                        'is_bot' => true,
                        'first_name' => 'Bot',
                    ],
                    'text' => 'Choose',
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $this->assertTrue($update->isCallbackQuery());
        $this->assertSame('menu:open', $update->getCallbackData());
        $this->assertSame(222, $update->getFrom()?->getId());
        $this->assertSame(-100, $update->getChatId());
        $this->assertSame('cb-1', $update->getCallbackQuery()?->getId());
    }

    public function testHydratesContact(): void
    {
        $update = TelegramMapper::exec(data: [
            'update_id' => 7,
            'message' => [
                'message_id' => 2,
                'date' => 1710000002,
                'chat' => [
                    'id' => 333,
                    'type' => 'private',
                ],
                'from' => [
                    'id' => 333,
                    'is_bot' => false,
                    'first_name' => 'Anna',
                ],
                'contact' => [
                    'phone_number' => '+79990001122',
                    'first_name' => 'Anna',
                    'user_id' => 333,
                ],
            ],
        ]);

        $contact = $update->getMessage()?->getContact();
        $this->assertSame('+79990001122', $contact?->getPhoneNumber());
        $this->assertSame(333, $contact?->getUserId());
    }

    public function testThrowsOnInvalidJson(): void
    {
        $this->expectException(InvalidPayloadException::class);
        TelegramMapper::exec(data: '{broken');
    }
}
