<?php

declare(strict_types=1);

namespace BotMapperFormatter\Tests\VK;

use BotMapperFormatter\Exception\InvalidPayloadException;
use BotMapperFormatter\VK\Mapper\Model\Confirmation;
use BotMapperFormatter\VK\Mapper\Model\MessageEvent;
use BotMapperFormatter\VK\Mapper\Model\MessageNew;
use BotMapperFormatter\VK\Mapper\VKMapper;
use PHPUnit\Framework\TestCase;

final class VKMapperTest extends TestCase
{
    public function testHydratesMessageNew(): void
    {
        $event = VKMapper::exec(data: [
            'group_id' => 111111111,
            'type' => 'message_new',
            'event_id' => '067ba91c03cfd888532208794a257f95a34dad0b',
            'v' => '5.199',
            'object' => [
                'client_info' => [
                    'button_actions' => ['text', 'callback', 'open_link'],
                    'keyboard' => true,
                    'inline_keyboard' => true,
                    'carousel' => true,
                    'lang_id' => 0,
                ],
                'message' => [
                    'date' => 1773940815,
                    'from_id' => 123052131,
                    'id' => 5,
                    'version' => 10000020,
                    'out' => 0,
                    'fwd_messages' => [],
                    'important' => false,
                    'is_hidden' => false,
                    'attachments' => [],
                    'conversation_message_id' => 2,
                    'text' => 'Hello from VK',
                    'peer_id' => 123052131,
                    'random_id' => 0,
                    'payload' => '{"button":1}',
                ],
            ],
            'secret' => 'secret-value',
        ]);

        $this->assertInstanceOf(MessageNew::class, $event);
        $this->assertSame('message_new', $event->getType());
        $this->assertSame(111111111, $event->getGroupId());
        $this->assertSame('Hello from VK', $event->getText());
        $this->assertSame(123052131, $event->getPeerId());
        $this->assertSame(['button' => 1], $event->getPayload());
        $this->assertSame('5.199', $event->getVersion());
        $this->assertTrue($event->getObject()->getClientInfo()?->getInlineKeyboard());
    }

    public function testHydratesMessageEvent(): void
    {
        $event = VKMapper::exec(data: json_encode([
            'type' => 'message_event',
            'group_id' => 161256065,
            'event_id' => '08285246239cca167e6d72d035920b5ea528c5ed',
            'v' => '5.199',
            'object' => [
                'user_id' => 325017603,
                'peer_id' => 2000000003,
                'event_id' => 'c9e90aab7b38',
                'payload' => ['button' => 'bot'],
                'conversation_message_id' => 2741,
            ],
        ], JSON_THROW_ON_ERROR));

        $this->assertInstanceOf(MessageEvent::class, $event);
        $this->assertSame(325017603, $event->getUserId());
        $this->assertSame(2000000003, $event->getPeerId());
        $this->assertSame('c9e90aab7b38', $event->getCallbackEventId());
        $this->assertSame(['button' => 'bot'], $event->getPayload());
        $this->assertSame('08285246239cca167e6d72d035920b5ea528c5ed', $event->getEventId());
    }

    public function testHydratesConfirmation(): void
    {
        $event = VKMapper::exec(data: [
            'type' => 'confirmation',
            'group_id' => 123456,
        ]);

        $this->assertInstanceOf(Confirmation::class, $event);
        $this->assertSame(123456, $event->getGroupId());
        $this->assertSame('confirmation', $event->getType());
    }

    public function testReturnsNullForUnsupportedType(): void
    {
        $this->assertNull(VKMapper::exec(data: [
            'type' => 'group_join',
            'group_id' => 1,
            'object' => ['user_id' => 1],
        ]));
    }

    public function testThrowsOnInvalidJson(): void
    {
        $this->expectException(InvalidPayloadException::class);
        VKMapper::exec(data: '{broken');
    }
}
