<?php

namespace Bx\Router\Otel\Event;

use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Psr\Http\Message\ServerRequestInterface;

class EventHelper
{
    public static function newSpanEvent(
        ServerRequestInterface $request,
        string $eventName,
        array $attributes = [],
        ?int $timestamp = null,
        ?BaseEventConfig $config = null,
        ?EventManager $eventManager = null
    ): void {
        $config = $config ?? DefaultEventConfig::getInstance();
        $event = new Event(
            $config->getModuleId(),
            $config->getNewSpanEventName(),
            [
                $request,
                $eventName,
                $attributes,
                $timestamp
            ]
        );

        $eventManager = $eventManager ?? EventManager::getInstance();
        $eventManager->send($event);
    }
    public static function newSpan(
        ServerRequestInterface $request,
        string $spanName,
        array $attributes = [],
        ?int $timestamp = null,
        ?BaseEventConfig $config = null,
        ?EventManager $eventManager = null
    ): void {
        $config = $config ?? DefaultEventConfig::getInstance();
        $event = new Event(
            $config->getModuleId(),
            $config->getNewSpanName(),
            [
                $request,
                $spanName,
                $attributes,
                $timestamp
            ]
        );

        $eventManager = $eventManager ?? EventManager::getInstance();
        $eventManager->send($event);
    }
}
