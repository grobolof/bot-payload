<?php

declare(strict_types=1);

namespace BotMapperFormatter\Tests\Telegram;

use BotMapperFormatter\Telegram\Message\Enum\Marker;
use BotMapperFormatter\Telegram\Message\Enum\Mod;
use BotMapperFormatter\Telegram\Message\Exception\TagsMismatchException;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Button;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\Keyboard;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\KeyboardInline;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\KeyboardRemove;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\ReplyMarkup;
use BotMapperFormatter\Telegram\TelegramFormatter;
use PHPUnit\Framework\TestCase;

final class TelegramFormatterTest extends TestCase
{
    public function testFormatsMarkdownV2AndEscapesReservedCharacters(): void
    {
        $text = 'Hello. World! '
            . Marker::MARKER_FONT_BOLD_OPEN->value
            . 'bold'
            . Marker::MARKER_FONT_BOLD_CLOSE->value
            . Marker::MARKER_LINEFEED->value
            . Marker::MARKER_URL_TEXT_OPEN->value
            . 'site'
            . Marker::MARKER_URL_TEXT_CLOSE->value
            . Marker::MARKER_URL_LINK_OPEN->value
            . 'https://example.com'
            . Marker::MARKER_URL_LINK_CLOSE->value;

        $formatted = TelegramFormatter::message(mod: Mod::MARKDOWN_V2, text: $text);

        $this->assertSame("Hello\\. World\\! *bold*\n[site](https://example\\.com)", $formatted);
    }

    public function testPreservesRealNewlines(): void
    {
        $formatted = TelegramFormatter::message(
            mod: Mod::MARKDOWN_V2,
            text: "Line 1\nLine 2"
        );

        $this->assertSame("Line 1\nLine 2", $formatted);
    }

    public function testThrowsWhenMarkersAreUnbalanced(): void
    {
        $this->expectException(TagsMismatchException::class);

        TelegramFormatter::message(
            mod: Mod::MARKDOWN_V2,
            text: Marker::MARKER_FONT_BOLD_OPEN->value . 'bold'
        );
    }

    public function testBuildsReplyKeyboardWithoutCallbackData(): void
    {
        $markup = TelegramFormatter::replyMarkup(model: new ReplyMarkup(
            keyboard: new Keyboard(buttonsPerRow: 2, resizeKeyboard: true, oneTimeKeyboard: false),
            buttons: [
                new Button(name: 'One'),
                new Button(name: 'Phone', requestContact: true),
                new Button(name: 'Two'),
            ],
        ));

        $this->assertSame(
            [
                'keyboard' => [
                    [
                        ['text' => 'One'],
                        ['text' => 'Phone', 'request_contact' => true],
                    ],
                    [
                        ['text' => 'Two'],
                    ],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ],
            $markup
        );
    }

    public function testBuildsInlineKeyboardByRows(): void
    {
        $markup = TelegramFormatter::replyMarkup(model: new ReplyMarkup(
            keyboard: new KeyboardInline(),
            buttons: [
                new Button(name: 'Open', row: 2, url: 'https://example.com'),
                new Button(name: 'Menu', row: 1, callbackData: 'menu'),
            ],
        ));

        $this->assertSame(
            [
                'inline_keyboard' => [
                    [
                        ['text' => 'Menu', 'callback_data' => 'menu'],
                    ],
                    [
                        ['text' => 'Open', 'url' => 'https://example.com'],
                    ],
                ],
            ],
            $markup
        );
    }

    public function testRemovesReplyKeyboard(): void
    {
        $markup = TelegramFormatter::replyMarkup(model: new ReplyMarkup(
            keyboard: new KeyboardRemove(),
        ));

        $this->assertSame(['remove_keyboard' => true], $markup);
    }

    public function testSendMessagePayload(): void
    {
        $payload = TelegramFormatter::sendMessage(
            chatId: 111,
            text: 'Hi.',
            parseMode: Mod::MARKDOWN_V2,
            replyMarkup: new ReplyMarkup(
                keyboard: new KeyboardInline(buttonsPerRow: 1),
                buttons: [new Button(name: 'Ok', callbackData: 'ok')],
            ),
        );

        $this->assertSame(111, $payload['chat_id']);
        $this->assertSame('Hi\\.', $payload['text']);
        $this->assertSame('MarkdownV2', $payload['parse_mode']);
        $this->assertSame(
            ['inline_keyboard' => [[['text' => 'Ok', 'callback_data' => 'ok']]]],
            $payload['reply_markup']
        );
    }

    public function testAnswerCallbackQueryPayload(): void
    {
        $payload = TelegramFormatter::answerCallbackQuery(
            callbackQueryId: 'cb-1',
            text: 'Done',
            showAlert: true,
        );

        $this->assertSame(
            [
                'callback_query_id' => 'cb-1',
                'text' => 'Done',
                'show_alert' => true,
            ],
            $payload
        );
    }

    public function testReplayMarkupAlias(): void
    {
        $markup = TelegramFormatter::replayMarkup(model: new ReplyMarkup(
            keyboard: new KeyboardRemove(),
        ));

        $this->assertSame(['remove_keyboard' => true], $markup);
    }
}
