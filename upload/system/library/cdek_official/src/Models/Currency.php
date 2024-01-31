<?php

namespace CDEK\Models;

class Currency
{
    private const DATA = [
            [
                'code'   => 1,
                'key'    => 'RUB',
            ],
            [
                'code'   => 2,
                'key'    => 'USD',
            ],
            [
                'code'   => 4,
                'key'    => 'EUR',
            ],
        ];

    public static function listCurrencies(): array
    {
        return self::DATA;
    }
}
