<?php

namespace Bx\Otel\Event;

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Request;
use BitrixPSR7\ServerRequest;
use Bx\Otel\BxRequestHelper;
use Bx\Otel\ConfigList;
use Bx\Otel\OTelFactory;
use Exception;
use Otel\Base\Interfaces\OTelSpanManagerInterface;
use Otel\Base\Util\RequestHelper;
use Psr\Http\Message\ServerRequestInterface;
use Bitrix\Main\Event;

class EventHandler
{
    private BaseEventConfig $config;

    public static function init(?BaseEventConfig $config = null, ?EventManager $eventManager = null): void
    {
        (new self($config))->sefRegister($eventManager);
    }

    public function __construct(?BaseEventConfig $config = null)
    {
        $this->config = $config ?? DefaultEventConfig::getInstance();
    }

    public function sefRegister(?EventManager $eventManager = null): void
    {
        $eventManager = $eventManager ?? EventManager::getInstance();
        $moduleId = $this->config->getModuleId();
        $eventManager->addEventHandler(
            $moduleId,
            $this->config->getNewSpanEventName(),
            [static::class, 'handleSpanEvent']
        );
        $eventManager->addEventHandler($moduleId, $this->config->getNewSpanName(), [static::class, 'handleNewSpan']);
    }

    public static function handleSpanEvent(Event $event): void
    {
        [$request, $eventName, $attributes, $timestamp] = $event->getParameters();
        $spanManager = static::getSpanFromRequest($request);
        if (!$spanManager) {
            return;
        }

        $spanManager->getSpan()->addEvent($eventName, $attributes, $timestamp);
    }

    public static function handleNewSpan(Event $event): void
    {
        [$request, $spanName, $attributes] = $event->getParameters();
        $spanManager = static::getSpanFromRequest($request);
        if (!$spanManager) {
            return;
        }

        $spanManager->createAndStartSpan($spanName, $attributes);
    }

    private static function getSpanFromRequest(ServerRequestInterface $request): ?OTelSpanManagerInterface
    {
        $spanManager = RequestHelper::getSpanManagerFromRequest($request);
        if ($spanManager instanceof OTelSpanManagerInterface) {
            return $spanManager;
        }

        $spanManager = BxRequestHelper::getSpanManagerFromRequest();
        return $spanManager instanceof OTelSpanManagerInterface ? $spanManager : null;
    }

    /**
     * @throws Exception
     */
    public static function onStart(): void
    {
        $useOtel = ConfigList::get(ConfigList::USE_OTEL, 'N') === 'Y';
        if (!$useOtel) {
            return;
        }

        $bxRequest = Application::getInstance()->getContext()->getRequest();
        $psrRequest = new ServerRequest($bxRequest);
        if (!static::isAllowRequest($psrRequest)) {
            return;
        }

        $spanManager = (new OTelFactory())->create($psrRequest);
        BxRequestHelper::provideSpanManagerToRequest($spanManager, $bxRequest);
        EventHandler::init();
    }

    private static function isAllowRequest(ServerRequestInterface $request): bool
    {
        $urls = ConfigList::get(ConfigList::OTEL_URLS, []);
        if (empty($urls)) {
            return true;
        }

        $path = $request->getUri()->getPath();
        foreach ($urls as $url) {
            if ($url === $path || preg_match("/$url/", $path) === 1) {
                return true;
            }
        }
        return false;
    }
}
