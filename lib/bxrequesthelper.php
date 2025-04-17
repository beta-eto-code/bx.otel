<?php

namespace Bx\Otel;

use Bitrix\Main\Context;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\NotSupportedException;
use Otel\Base\Interfaces\OTelSpanManagerInterface;
use Otel\Base\Util\RequestHelper;

class BxRequestHelper
{
    public static function getSpanManagerFromRequest(?HttpRequest $request = null): ?OTelSpanManagerInterface
    {
        $request = $request ?? Context::getCurrent()->getRequest();
        $spanManager = $request->get(RequestHelper::SPAN_MANAGER_ATTRIBUTE);
        return $spanManager instanceof OTelSpanManagerInterface ? $spanManager : null;
    }

    /**
     * @throws NotSupportedException
     */
    public static function provideSpanManagerToRequest(
        OTelSpanManagerInterface $spanManager,
        ?HttpRequest $request = null
    ): HttpRequest {
        $request = $request ?? Context::getCurrent()->getRequest();
        $values = $request->getValues();
        $values[RequestHelper::SPAN_MANAGER_ATTRIBUTE] = $spanManager;
        $request->set($values);
        return $request;
    }
}
