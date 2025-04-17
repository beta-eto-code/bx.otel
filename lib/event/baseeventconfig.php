<?php

namespace Bx\Otel\Event;

abstract class BaseEventConfig
{
    private function __construct() {}
    private function __clone() {}

    abstract public function getModuleId(): string;
    abstract public function getNewSpanEventName(): string;
    abstract public function getNewSpanName(): string;

    public static function getInstance(): BaseEventConfig
    {
        return new static();
    }
}
