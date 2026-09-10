# bot-mapper-formatter

PHP-библиотека для чат-ботов Telegram и VK.

Пакет **не ходит в API**. Он делает две вещи:

1. **Гидратация** — входящий JSON вебхука превращается в PHP-объекты.
2. **Форматирование** — исходящие текст и клавиатуры собираются в массив параметров, который можно сразу отдать HTTP-клиенту (`sendMessage`, `messages.send` и т.д.).

```
мессенджер  →  JSON  →  Mapper  →  ваш код  →  Formatter  →  JSON/params  →  мессенджер
```

## Установка

```bash
composer require grobolof/bot-mapper-formatter
```

PHP `^8.2`, Symfony Serializer `^6.4 || ^7.4 || ^8.0`.

Если пакет ещё не на Packagist, подключите репозиторий:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:grobolof/bot-mapper-formatter.git"
    }
  ]
}
```

---

## Telegram

### Что присылает Telegram (гидратация)

После `setWebhook` Telegram шлёт `POST` с JSON-объектом [Update](https://core.telegram.org/bots/api#update). Обязательное поле одно: `update_id`. Дальше присутствует **не больше одного** события.

Пакет гидратирует три самых нужных типа:

| Поле Update | Когда приходит | Что внутри |
| --- | --- | --- |
| `message` | Пользователь написал текст, отправил контакт, фото и т.д. | `message_id`, `date`, `chat`, `from`, `text`, `entities`, `contact`, вложения |
| `edited_message` | Пользователь отредактировал сообщение | тот же объект Message |
| `callback_query` | Нажата inline-кнопка | `id`, `from`, `data`, `chat_instance`, `message` |

Остальные типы Update (`inline_query`, `my_chat_member`, …) библиотека не маппит: `TelegramMapper::exec()` всё равно вернёт `TelegramUpdate` с одним `updateId`.

#### Текстовое сообщение / команда

```json
{
  "update_id": 123456,
  "message": {
    "message_id": 10,
    "date": 1710000000,
    "text": "/start payload",
    "entities": [
      { "offset": 0, "length": 6, "type": "bot_command" }
    ],
    "from": {
      "id": 111,
      "is_bot": false,
      "first_name": "Ivan",
      "last_name": "Petrov",
      "username": "ivan",
      "language_code": "ru"
    },
    "chat": {
      "id": 111,
      "type": "private",
      "first_name": "Ivan",
      "username": "ivan"
    }
  }
}
```

```php
use BotMapperFormatter\Telegram\Mapper\TelegramMapper;

$update = TelegramMapper::exec($requestJson); // array|string

$update->getUpdateId();          // 123456
$update->isMessage();             // true
$update->getChatId();            // 111
$update->getFrom()?->getId();    // 111
$update->getFrom()?->getUsername();// ivan
$update->getText();               // /start payload
$update->getMessage()?->getBotCommand(); // start
```

#### Контакт (кнопка «Поделиться номером»)

```json
{
  "update_id": 7,
  "message": {
    "message_id": 2,
    "date": 1710000002,
    "chat": { "id": 333, "type": "private" },
    "from": { "id": 333, "is_bot": false, "first_name": "Anna" },
    "contact": {
      "phone_number": "+79990001122",
      "first_name": "Anna",
      "user_id": 333
    }
  }
}
```

```php
$update->getMessage()?->getContact()?->getPhoneNumber(); // +79990001122
```

Вложения (`photo`, `document`, `voice`, `audio`, `video`, `sticker`, `location`, `web_app_data`) сохраняются как массивы, без отдельной модели на каждый тип.

#### Нажатие inline-кнопки

```json
{
  "update_id": 99,
  "callback_query": {
    "id": "cb-1",
    "from": { "id": 222, "is_bot": false, "first_name": "Anna" },
    "chat_instance": "chat-instance",
    "data": "menu:open",
    "message": {
      "message_id": 5,
      "date": 1710000001,
      "text": "Choose",
      "chat": { "id": 222, "type": "private" }
    }
  }
}
```

```php
$update->isCallbackQuery();                         // true
$update->getCallbackData();                         // menu:open
$update->getCallbackQuery()?->getId();              // cb-1  → answerCallbackQuery
$update->getChatId();                               // 222
```

После каждого `callback_query` Telegram ждёт `answerCallbackQuery`, иначе у пользователя крутится прогресс на кнопке.

Некорректный JSON бросает `BotMapperFormatter\Exception\InvalidPayloadException`.

### Что принимать в ответ (отправка в Telegram)

Библиотека собирает параметры методов Bot API. Отправку делает ваш HTTP-клиент, например:

`POST https://api.telegram.org/bot<token>/sendMessage`

Если тело запроса `application/json`, массив можно кодировать целиком. Если `application/x-www-form-urlencoded`, поле `reply_markup` нужно отдельно `json_encode`.

#### `sendMessage`

Поля, которые заполняет пакет:

| Параметр | Откуда | Обязателен |
| --- | --- | --- |
| `chat_id` | `int\|string` | да |
| `text` | строка; при `parseMode` ещё прогоняется через MarkdownV2 | да |
| `parse_mode` | `Mod::MARKDOWN_V2` → `MarkdownV2` | нет |
| `reply_markup` | объект клавиатуры | нет |
| `disable_notification` | bool | нет |
| `reply_to_message_id` | int | нет |
| `message_thread_id` | int, топик форума | нет |

```php
use BotMapperFormatter\Telegram\Message\Enum\Marker;
use BotMapperFormatter\Telegram\Message\Enum\Mod;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Button;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\KeyboardInline;
use BotMapperFormatter\Telegram\ReplyMarkup\Model\ReplyMarkup;
use BotMapperFormatter\Telegram\TelegramFormatter;

$payload = TelegramFormatter::sendMessage(
    chatId: $update->getChatId(),
    text: Marker::MARKER_FONT_BOLD_OPEN->value
        . 'Привет'
        . Marker::MARKER_FONT_BOLD_CLOSE->value
        . '. Это бот.',
    parseMode: Mod::MARKDOWN_V2,
    replyMarkup: new ReplyMarkup(
        keyboard: new KeyboardInline(buttonsPerRow: 2),
        buttons: [
            new Button(name: 'Меню', callbackData: 'menu:open'),
            new Button(name: 'Сайт', url: 'https://example.com'),
        ],
    ),
);
// [
//   'chat_id' => 111,
//   'text' => '*Привет*\\. Это бот\\.',
//   'parse_mode' => 'MarkdownV2',
//   'reply_markup' => ['inline_keyboard' => [[...], ...]],
// ]
```

Без `parseMode` текст уходит как есть, `parse_mode` в payload нет.

#### MarkdownV2

Telegram требует экранировать `_ * [ ] ( ) ~ \` > # + - = | { } . !`. Пакет экранирует их сам. Разметку размечайте **маркерами**, а не сырым Markdown — иначе звёздочки в тексте пользователя сломают парсер.

| Маркер | Результат |
| --- | --- |
| `#__MARKER_FONT_BOLD_OPEN__#` / `_CLOSE__#` | `*жирный*` |
| `#__MARKER_URL_TEXT_OPEN__#` / `_CLOSE__#` | `[текст](...)` |
| `#__MARKER_URL_LINK_OPEN__#` / `_CLOSE__#` | `(https://...)` |
| `#__MARKER_LOWERCASE_CODE_OPEN__#` / `_CLOSE__#` | `` `code` `` |
| `#__MARKER_LINEFEED__#` | перевод строки |

Обычные `\n` тоже сохраняются. Несбалансированные маркеры бросают `TagsMismatchException`.

Можно вызвать только форматтер текста:

```php
TelegramFormatter::message(Mod::MARKDOWN_V2, $text);
```

#### Клавиатуры (`reply_markup`)

Три варианта, все через `TelegramFormatter::replyMarkup()` / `sendMessage(..., replyMarkup: ...)`.

**1. Reply-клавиатура** — кнопки вместо системной клавиатуры. Нажатие присылает обычный `message` с текстом кнопки. У reply-кнопок **нет** `callback_data`.

```php
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\Keyboard;

new ReplyMarkup(
    keyboard: new Keyboard(buttonsPerRow: 2, resizeKeyboard: true, oneTimeKeyboard: true),
    buttons: [
        new Button(name: 'Да'),
        new Button(name: 'Телефон', requestContact: true),
        new Button(name: 'Гео', requestLocation: true),
    ],
);
```

Уходит как:

```json
{
  "keyboard": [
    [{ "text": "Да" }, { "text": "Телефон", "request_contact": true }],
    [{ "text": "Гео", "request_location": true }]
  ],
  "resize_keyboard": true,
  "one_time_keyboard": true
}
```

Если `buttonsPerRow` не задан, ряд берётся из `Button::$row` (нумерация с 1).

Дополнительно у `Keyboard`: `isPersistent`, `selective`, `inputFieldPlaceholder`.

**2. Inline-клавиатура** — кнопки под сообщением. Нажатие присылает `callback_query` (или открывает URL / inline-режим).

Каждая кнопка должна содержать ровно одно действие: `callbackData` (1–64 байта), `url` или `switchInlineQuery`.

```php
new ReplyMarkup(
    keyboard: new KeyboardInline(),
    buttons: [
        new Button(name: 'Открыть', row: 1, callbackData: 'open'),
        new Button(name: 'Сайт', row: 2, url: 'https://example.com'),
    ],
);
```

```json
{
  "inline_keyboard": [
    [{ "text": "Открыть", "callback_data": "open" }],
    [{ "text": "Сайт", "url": "https://example.com" }]
  ]
}
```

**3. Удалить reply-клавиатуру**

```php
use BotMapperFormatter\Telegram\ReplyMarkup\Model\Keyboard\KeyboardRemove;

new ReplyMarkup(keyboard: new KeyboardRemove());
// { "remove_keyboard": true }
```

#### `answerCallbackQuery`

```php
TelegramFormatter::answerCallbackQuery(
    callbackQueryId: $update->getCallbackQuery()->getId(),
    text: 'Готово',
    showAlert: false,
);
// { "callback_query_id": "cb-1", "text": "Готово" }
```

Опционально: `showAlert`, `url`, `cacheTime`.

Старый метод `replayMarkup()` оставлен как алиас `replyMarkup()`.

---

## VK

### Что присылает VK (гидратация)

Callback API / Bots Long Poll шлёт JSON:

```json
{
  "type": "<тип события>",
  "object": {},
  "group_id": 1,
  "event_id": "...",
  "v": "5.199",
  "secret": "..."
}
```

`VKMapper::exec()` понимает три типа, нужных боту. Остальные (`group_join`, `message_reply`, …) возвращают `null` — их можно игнорировать.

Ответ серверу на вебхук: строка `ok` (кроме `confirmation`). Сама отправка сообщения — отдельный запрос к `messages.send`.

#### `confirmation` — проверка Callback URL

```json
{
  "type": "confirmation",
  "group_id": 123456
}
```

```php
use BotMapperFormatter\VK\Mapper\Model\Confirmation;
use BotMapperFormatter\VK\Mapper\VKMapper;

$event = VKMapper::exec($requestJson);

if ($event instanceof Confirmation) {
    echo $confirmationToken; // строка из настроек сообщества, не JSON
    return;
}
```

#### `message_new` — входящее сообщение или нажатие text-кнопки

Для API **5.103+** `object` содержит `message` и `client_info`. Text-кнопка обычной клавиатуры тоже приходит как `message_new`: текст кнопки в `message.text`, ваши данные — в `message.payload` (JSON-строка).

```json
{
  "group_id": 111111111,
  "type": "message_new",
  "event_id": "067ba91c03cfd888532208794a257f95a34dad0b",
  "v": "5.199",
  "object": {
    "client_info": {
      "button_actions": ["text", "callback", "open_link", "open_app", "location"],
      "keyboard": true,
      "inline_keyboard": true,
      "carousel": true,
      "lang_id": 0
    },
    "message": {
      "date": 1773940815,
      "from_id": 123052131,
      "id": 5,
      "version": 10000020,
      "out": 0,
      "fwd_messages": [],
      "important": false,
      "is_hidden": false,
      "attachments": [],
      "conversation_message_id": 2,
      "text": "Привет",
      "peer_id": 123052131,
      "random_id": 0,
      "payload": "{\"button\":1}"
    }
  },
  "secret": "optional"
}
```

```php
use BotMapperFormatter\VK\Mapper\Model\MessageNew;

$event = VKMapper::exec($requestJson);

if ($event instanceof MessageNew) {
    $event->getPeerId();   // куда отвечать (messages.send)
    $event->getFromId();   // кто написал
    $event->getText();     // Привет
    $event->getPayload();   // ['button' => 1] или null
    $event->getObject()->getClientInfo()?->getInlineKeyboard(); // true
}
```

`payload` в VK приходит строкой — пакет сам делает `json_decode`. Сырая строка: `getRawPayload()`.

Вложения, пересланные сообщения, `reply_message`, `geo`, `action`, `keyboard` доступны как массивы с сообщения.

`client_info.button_actions` — какие типы кнопок клиент умеет (`text`, `callback`, `open_link`, …). Имеет смысл не слать `callback`, если его нет в списке.

#### `message_event` — нажатие callback-кнопки

```json
{
  "type": "message_event",
  "group_id": 161256065,
  "event_id": "08285246239cca167e6d72d035920b5ea528c5ed",
  "object": {
    "user_id": 325017603,
    "peer_id": 2000000003,
    "event_id": "c9e90aab7b38",
    "payload": { "button": "bot" },
    "conversation_message_id": 2741
  }
}
```

Здесь два разных `event_id`:

- корневой — идентификатор события Callback API;
- `object.event_id` — одноразовый id нажатия (живёт ~1 минуту), его нужно отдать в `messages.sendMessageEventAnswer`.

```php
use BotMapperFormatter\VK\Mapper\Model\MessageEvent;

if ($event instanceof MessageEvent) {
    $event->getCallbackEventId(); // c9e90aab7b38 → sendMessageEventAnswer
    $event->getUserId();
    $event->getPeerId();
    $event->getPayload();         // ['button' => 'bot'] (уже массив)
}
```

### Что принимать в ответ (отправка в VK)

#### `messages.send`

`POST https://api.vk.ru/method/messages.send`

| Параметр | Откуда | Обязателен |
| --- | --- | --- |
| `peer_id` | из входящего события | да |
| `message` | текст как есть (у VK нет MarkdownV2) | да, если нет `attachment` |
| `random_id` | ваш int или случайный | да |
| `keyboard` | **JSON-строка**, не объект | нет |
| `attachment` | `photo123_456`, … | нет |
| `reply_to` | id сообщения | нет |

Плюс ваш `access_token` и `v`.

```php
use BotMapperFormatter\VK\Formatter\Enum\ButtonColor;
use BotMapperFormatter\VK\Formatter\Enum\ButtonType;
use BotMapperFormatter\VK\Formatter\Model\Button;
use BotMapperFormatter\VK\Formatter\Model\Keyboard;
use BotMapperFormatter\VK\Formatter\Model\ReplyMarkup;
use BotMapperFormatter\VK\Formatter\VKFormatter;

$payload = VKFormatter::sendMessage(
    peerId: $event->getPeerId(),
    text: 'Выберите действие',
    replyMarkup: new ReplyMarkup(
        keyboard: new Keyboard(oneTime: false, inline: true, buttonsPerRow: 2),
        buttons: [
            new Button(label: 'Да', type: ButtonType::CALLBACK, payload: ['cmd' => 'yes'], color: ButtonColor::POSITIVE),
            new Button(label: 'Сайт', type: ButtonType::OPEN_LINK, link: 'https://example.com'),
        ],
    ),
);
// [
//   'peer_id' => 123052131,
//   'message' => 'Выберите действие',
//   'random_id' => <int>,
//   'keyboard' => '{"one_time":false,"inline":true,"buttons":[...]}',
// ]
```

#### Клавиатура VK

Формат [официальной клавиатуры](https://dev.vk.ru/ru/api/bots/development/keyboard):

```json
{
  "one_time": false,
  "inline": true,
  "buttons": [
    [
      {
        "action": { "type": "callback", "label": "Да", "payload": "{\"cmd\":\"yes\"}" },
        "color": "positive"
      }
    ]
  ]
}
```

| `Keyboard` | Смысл |
| --- | --- |
| `oneTime: true` | скрыть после нажатия (только обычная клавиатура) |
| `inline: true` | кнопки под сообщением |
| `buttonsPerRow` | нарезка рядов; иначе используется `Button::$row` |

| `ButtonType` | Когда жать | Что придёт боту |
| --- | --- | --- |
| `TEXT` | обычная / inline | `message_new`, текст + `payload` |
| `CALLBACK` | inline | `message_event` + `payload` |
| `OPEN_LINK` | нужен `link` | ничего, клиент открывает URL |
| `OPEN_APP` | нужны `appId`, опционально `ownerId`/`hash` | открывает VK Mini App |
| `LOCATION` | запрос геоточки | `message_new` с `geo` |

Цвета (`ButtonColor`) для `text` и `callback`: `primary`, `secondary`, `positive`, `negative`.

`payload` можно передать массивом — пакет сам сделает JSON-строку (лимит VK — 255 символов).

Скрыть клавиатуру:

```php
VKFormatter::keyboardRemove();
// { "one_time": true, "inline": false, "buttons": [] }
```

Эту структуру нужно `json_encode` и передать в `messages.send` как `keyboard`. Либо используйте `VKFormatter::keyboardJson($markup)`.

Лимиты VK: до 10 рядов; в ряду до 4 кнопок у обычной клавиатуры и до 5 у inline.

#### `messages.sendMessageEventAnswer`

Обязателен после `message_event`, иначе у пользователя крутится индикатор на кнопке.

```php
$payload = VKFormatter::eventAnswer(
    eventId: $event->getCallbackEventId(),
    userId: $event->getUserId(),
    peerId: $event->getPeerId(),
    eventData: VKFormatter::snackbar('Сохранено'),
);
// [
//   'event_id' => 'c9e90aab7b38',
//   'user_id' => 325017603,
//   'peer_id' => 2000000003,
//   'event_data' => '{"type":"show_snackbar","text":"Сохранено"}',
// ]
```

Готовые `event_data`:

```php
VKFormatter::snackbar('Текст до 90 символов');
VKFormatter::openLink('https://example.com');
```

---

## Минимальный цикл бота

### Telegram

```php
$update = TelegramMapper::exec($json);

if ($update->isCallbackQuery()) {
    $http->post('answerCallbackQuery', TelegramFormatter::answerCallbackQuery(
        callbackQueryId: $update->getCallbackQuery()->getId(),
    ));
}

$http->post('sendMessage', TelegramFormatter::sendMessage(
    chatId: $update->getChatId(),
    text: 'Ок',
));
```

### VK

```php
$event = VKMapper::exec($json);

if ($event instanceof Confirmation) {
    echo $confirmationToken;
    return;
}

echo 'ok';

if ($event instanceof MessageEvent) {
    $http->post('messages.sendMessageEventAnswer', VKFormatter::eventAnswer(
        eventId: $event->getCallbackEventId(),
        userId: $event->getUserId(),
        peerId: $event->getPeerId(),
        eventData: VKFormatter::snackbar('Ок'),
    ));
}

if ($event instanceof MessageNew) {
    $http->post('messages.send', VKFormatter::sendMessage(
        peerId: $event->getPeerId(),
        text: 'Ок',
    ));
}
```

---

## Тесты

```bash
composer install
vendor/bin/phpunit
```
