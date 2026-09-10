<?php

declare(strict_types=1);

namespace BotPayload\Tests\VK;

use BotPayload\VK\Formatter\Enum\ButtonColor;
use BotPayload\VK\Formatter\Enum\ButtonType;
use BotPayload\VK\Formatter\Model\Button;
use BotPayload\VK\Formatter\Model\Keyboard;
use BotPayload\VK\Formatter\Model\ReplyMarkup;
use BotPayload\VK\Formatter\VKFormatter;
use PHPUnit\Framework\TestCase;

final class VKFormatterTest extends TestCase
{
    public function testBuildsKeyboardJson(): void
    {
        $markup = new ReplyMarkup(
            keyboard: new Keyboard(oneTime: true, inline: false, buttonsPerRow: 2),
            buttons: [
                new Button(label: 'One', payload: ['cmd' => 'one'], color: ButtonColor::PRIMARY),
                new Button(label: 'Two', type: ButtonType::CALLBACK, payload: '{"cmd":"two"}', color: ButtonColor::POSITIVE),
                new Button(label: 'Site', type: ButtonType::OPEN_LINK, link: 'https://example.com'),
            ],
        );

        $keyboard = VKFormatter::keyboard(model: $markup);

        $this->assertTrue($keyboard['one_time']);
        $this->assertFalse($keyboard['inline']);
        $this->assertSame('text', $keyboard['buttons'][0][0]['action']['type']);
        $this->assertSame('{"cmd":"one"}', $keyboard['buttons'][0][0]['action']['payload']);
        $this->assertSame('primary', $keyboard['buttons'][0][0]['color']);
        $this->assertSame('callback', $keyboard['buttons'][0][1]['action']['type']);
        $this->assertSame('open_link', $keyboard['buttons'][1][0]['action']['type']);
        $this->assertSame('https://example.com', $keyboard['buttons'][1][0]['action']['link']);
        $this->assertArrayNotHasKey('color', $keyboard['buttons'][1][0]);
    }

    public function testSendMessageIncludesKeyboardJson(): void
    {
        $payload = VKFormatter::sendMessage(
            peerId: 123,
            text: 'Hello',
            replyMarkup: new ReplyMarkup(
                keyboard: new Keyboard(inline: true),
                buttons: [new Button(label: 'Ok', type: ButtonType::CALLBACK, payload: ['ok' => true])],
            ),
            randomId: 42,
        );

        $this->assertSame(123, $payload['peer_id']);
        $this->assertSame('Hello', $payload['message']);
        $this->assertSame(42, $payload['random_id']);
        $this->assertIsString($payload['keyboard']);

        $decoded = json_decode($payload['keyboard'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($decoded['inline']);
        $this->assertSame('callback', $decoded['buttons'][0][0]['action']['type']);
    }

    public function testEventAnswerWithSnackbar(): void
    {
        $payload = VKFormatter::eventAnswer(
            eventId: 'c9e90aab7b38',
            userId: 325017603,
            peerId: 2000000003,
            eventData: VKFormatter::snackbar(text: 'Saved'),
        );

        $this->assertSame('c9e90aab7b38', $payload['event_id']);
        $this->assertSame(
            '{"type":"show_snackbar","text":"Saved"}',
            $payload['event_data']
        );
    }

    public function testKeyboardRemove(): void
    {
        $this->assertSame(
            [
                'one_time' => true,
                'inline' => false,
                'buttons' => [],
            ],
            VKFormatter::keyboardRemove()
        );
    }
}
