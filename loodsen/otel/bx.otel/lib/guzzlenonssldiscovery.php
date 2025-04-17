<?php

namespace Bx\Router\Otel;

use GuzzleHttp\Client;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use Psr\Http\Client\ClientInterface;

class GuzzleNonSSLDiscovery implements DiscoveryInterface
{
    public function available(): bool
    {
        return class_exists(Client::class) &&
            is_a(Client::class, ClientInterface::class, true);
    }

    public function create(mixed $options): ClientInterface
    {
        $options['verify'] = false;

        return new Client($options);
    }
}
