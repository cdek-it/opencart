<?php

namespace CDEK\Models;

class Currency
{
    /**
     * 1    Рубль
     * 2    Тенге
     * 3    Доллар
     * 4    Евро
     * 5    Фунт стерлингов
     * 6    Юань
     * 7    Белорусские рубли
     * 8    Гривна
     * 9    Киргизский сом
     * 10    Армянский драм
     * 11    Турецкая лира
     * 12    Тайский бат
     * 13    Вона
     * 14    Дирхам
     * 15    Сум
     * 16    Тугрик
     * 17    Злотый
     * 18    Манат
     * 19    Лари
     * 55    Японская иена
     */
    private const DATA = [
            [
                'code'   => 1,
                'key'    => 'RUB',
            ],
            [
                'code'   => 2,
                'key'    => 'KZT',
            ],
            [
                'code'   => 3,
                'key'    => 'USD',
            ],
            [
                'code'   => 4,
                'key'    => 'EUR',
            ],
            [
                'code'   => 5,
                'key'    => 'GBP',
            ],
            [
                'code'   => 6,
                'key'    => 'CNY',
            ],
            [
                'code'   => 7,
                'key'    => 'BYN',
            ],
            [
                'code'   => 8,
                'key'    => 'UAH',
            ],
            [
                'code'   => 9,
                'key'    => 'KGS',
            ],
            [
                'code'   => 10,
                'key'    => 'AMD',
            ],
            [
                'code'   => 11,
                'key'    => 'TRY',
            ],
            [
                'code'   => 12,
                'key'    => 'THB',
            ],
            [
                'code'   => 13,
                'key'    => 'KRW',
            ],
            [
                'code'   => 14,
                'key'    => 'AED',
            ],
            [
                'code'   => 15,
                'key'    => 'UZS',
            ],
            [
                'code'   => 16,
                'key'    => 'MNT',
            ],
            [
                'code'   => 17,
                'key'    => 'PLN',
            ],
            [
                'code'   => 18,
                'key'    => 'AZN',
            ],
            [
                'code'   => 19,
                'key'    => 'GEL',
            ],
            [
                'code'   => 55,
                'key'    => 'JPY',
            ],
        ];

    public static function listCurrencies(): array
    {
        return self::DATA;
    }
}
