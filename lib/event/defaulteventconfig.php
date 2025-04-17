<?php

namespace Bx\Otel\Event;

class DefaultEventConfig extends BaseEventConfig
{
    public function getModuleId(): string
    {
        return 'bx.otel';
    }

    public function getNewSpanEventName(): string
    {
        return 'bx.otel.new_span_event';
    }

    public function getNewSpanName(): string
    {
        return 'bx.otel.new_span';
    }
}
