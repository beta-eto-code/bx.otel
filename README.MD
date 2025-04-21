# OpenTelemetry модуль для использования Bitrix

**Установка**
```shell
composer require beta/bx.otel
```

**Примеры добавления OTEL события и спана**

```php

use Bitrix\Main\Context;
use Bitrix\Main\Event;
use Bx\Otel\Event\EventHelper;

$request = Context::getCurrent()->getRequest();

// Регистрация новой области (span) через вспомогательный класс
EventHelper::newSpan(
    $request,
    'IncludedSpanName',
    [
        'task_id' => $taskId,
    ]
);

// Регистрация новой области (span) через событие bitrix
new Event('bx.otel', 'new_span', [
    $request,
    'IncludedSpanName',
    [
        'task_id' => $taskId,
    ]
]);

// Регистрация нового события текущей области (span) через вспомогательный класс
EventHelper::newSpanEvent(
    $request,
    'EventName',
    [
        'task_id' => $taskId,
    ]
);


// Регистрация нового события текущей области (span) через событие bitrix
new Event('bx.otel', 'new_span_event', [
    $request,
    'EventName',
    [
        'task_id' => $taskId,
    ]
]);
```
