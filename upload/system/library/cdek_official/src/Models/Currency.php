<?php

namespace CDEK\Models;

class Currency
{
    private const DATA = [
            [
                'code'   => 'RUB',
                'key'    => 'cdek_shipping__currency_rub',
            ],
            [
                'code'   => 'USD',
                'key'    => 'cdek_shipping__currency_usd',
            ],
            [
                'code'   => 'EUR',
                'key'    => 'cdek_shipping__currency_eur',
            ],
        ];

    public static function listCurrencies(): array
    {
        return self::DATA;
    }
}
