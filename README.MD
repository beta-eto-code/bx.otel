# OpenTelemetry модуль для использования Bitrix

**Пример добавления OTEL события и спана**

```php

    
    new \Bitrix\Main\Event('bx.otel', 'new_span', [
        $request,
        'IncludedSpanName',
        [
            'task_id' => $taskId,
        ]
    ]);
    
    new \Bitrix\Main\Event('bx.otel', 'new_span_event', [
        $request,
        'EventName',
        [
            'task_id' => $taskId,
        ]
    ]);

```
