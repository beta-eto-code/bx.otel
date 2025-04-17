<?php

namespace Bx\Otel;

use Bitrix\Main\Config\Option;

class ConfigList
{
    const MODULE_NAME = 'bx.otel';
    const USE_OTEL = 'USE_OTEL';
    const OTEL_URLS = 'OTEL_URLS';
    const OTEL_HOST = 'OTEL_HOST';
    const OTEL_SSL_VERIFY_DISABLE = 'OTEL_SSL_VERIFY_DISABLE';
    const OTEL_LOGIN = 'OTEL_LOGIN';
    const OTEL_PASSWORD = 'OTEL_PASSWORD';

    public static function getOptionsTab(): array
    {
        return [
            [
                'tab' => "Телеметрия",
                'options' => [
                    ConfigList::USE_OTEL => [
                        'label' => 'Включить трейсинг запросов',
                        'name' => ConfigList::USE_OTEL,
                        'type' => 'checkbox',
                        'multiple' => false
                    ],
                    ConfigList::OTEL_HOST => [
                        'label' => 'Адрес OTEL сервера',
                        'name' => ConfigList::OTEL_HOST,
                        'type' => 'string',
                        'multiple' => false,
                        'validator' => function ($value) {
                             return filter_var($value, FILTER_VALIDATE_URL);
                        }
                    ],
                    ConfigList::OTEL_SSL_VERIFY_DISABLE => [
                        'label' => 'Отключить проверку SSL сертификата',
                        'name' => ConfigList::OTEL_SSL_VERIFY_DISABLE,
                        'type' => 'checkbox',
                        'multiple' => false
                    ],
                    ConfigList::OTEL_LOGIN => [
                        'label' => 'Логин OTEL сервера',
                        'name' => ConfigList::OTEL_LOGIN,
                        'type' => 'string',
                        'multiple' => false
                    ],
                    ConfigList::OTEL_PASSWORD => [
                        'label' => 'Пароль OTEL сервера',
                        'name' => ConfigList::OTEL_PASSWORD,
                        'type' => 'string',
                        'multiple' => false
                    ],
                    ConfigList::OTEL_URLS => [
                        'label' => 'Список URL разрешенных для отправки в OTEL (по-умолчанию все разрешены)',
                        'name' => ConfigList::OTEL_URLS,
                        'type' => 'string',
                        'multiple' => true
                    ],
                ],
            ],
        ];
    }

    public static function get(string $key, $default = null)
    {
        $originalValue = Option::get(static::MODULE_NAME, $key, $default);
        if (static::isComplexValue($key) && !empty($originalValue)) {
            return json_decode($originalValue, true) ?: [];
        }

        return $originalValue;
    }

    private static function isComplexValue(string $key): bool
    {
        return in_array($key, [
            static::OTEL_URLS,
        ]);
    }
}
