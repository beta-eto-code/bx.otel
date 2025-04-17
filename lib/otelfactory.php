<?php

namespace Bx\Router\Otel;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Exception;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Otel\Base\Interfaces\OTelFactoryInterface;
use Otel\Base\Interfaces\OTelSpanManagerInterface;
use Otel\Base\OTelSpanManager;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class OTelFactory implements OTelFactoryInterface
{
    private static ?array $options = null;
    private ?iterable $eventIterator;

    public function __construct(?iterable $eventIterator = null)
    {
        $this->eventIterator = $eventIterator;
    }

    /**
     * @throws Exception
     */
    public function create(ServerRequestInterface $request): OTelSpanManagerInterface
    {
        $options = self::getOptions();
        $host = $options[ConfigList::OTEL_HOST];
        if (empty($host)) {
            return $this->createDefaultManager($request);
        }

        try {
            $isSSLVerifyDisable = $options[ConfigList::OTEL_SSL_VERIFY_DISABLE] ?? false;
            if ($isSSLVerifyDisable) {
                Discovery::setDiscoverers([
                    GuzzleNonSSLDiscovery::class
                ]);
            }

            $transport = (new OtlpHttpTransportFactory())->create($host, ContentTypes::JSON);
            $exporter = new SpanExporter($transport);
        } catch (Throwable) {
            return $this->createDefaultManager($request);
        }

        $tracerProvider = new TracerProvider(
            new SimpleSpanProcessor(
                $exporter
            )
        );

        return new OTelSpanManager($tracerProvider, $this->eventIterator);
    }

    /**
     * @throws Exception
     */
    private function createDefaultManager(ServerRequestInterface $request): OTelSpanManagerInterface
    {
        return (new \Otel\Base\OTelFactory(
            $_SERVER['DOCUMENT_ROOT'] . '/otel.json',
            $this->eventIterator
        ))->create($request);
    }

    public static function getOptions(): array
    {
        if (!is_null(self::$options)) {
            return self::$options;
        }

        $optionsTabs = ConfigList::getOptionsTab();
        $mid = ConfigList::MODULE_NAME;
        $result = [];


        foreach ($optionsTabs as $optionTab) {
            foreach ($optionTab['options'] as $name => $value) {
                if (is_string($value)) {
                    $optionName = $name;
                } else if (is_array($value)) {
                    $optionName = $value['name'] ?? null;
                    if (!$optionName) {
                        continue;
                    }
                } else {
                    continue;
                }

                $optionValue = (string)Option::get($mid, $optionName, $value['default'] ?? "");
                $decodedValue = json_decode($optionValue, true) ?? null;
                if ($decodedValue) {
                    $optionValue = $decodedValue;
                }

                $result[$name] = $optionValue;
            }
        }
        return self::$options = $result;
    }
}
