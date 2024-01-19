<?php

namespace CDEK\Models;

use CDEK\RegistrySingleton;
use RuntimeException;

class Tariffs
{
    private const LOCALE_PREFIX = 'cdek_shipping__tariff_name_';
    private const DOOR_DOOR = 1;
    private const DOOR_OFFICE = 2;
    private const OFFICE_DOOR = 3;
    private const OFFICE_OFFICE = 4;
    private const DOOR_PICKUP = 6;
    private const OFFICE_PICKUP = 7;
    private const PICKUP_DOOR = 8;
    private const PICKUP_OFFICE = 9;
    private const PICKUP_PICKUP = 10;

    private const TARIFF_DATA
        = [
            136 => [
                'mode' => self::OFFICE_OFFICE,
            ],
            137 => [
                'mode' => self::OFFICE_DOOR,
            ],
            138 => [
                'mode' => self::DOOR_OFFICE,
            ],
            139 => [
                'mode' => self::DOOR_DOOR,
            ],
            184 => [
                'mode' => self::DOOR_DOOR,
            ],
            185 => [
                'mode' => self::OFFICE_OFFICE,
            ],
            186 => [
                'mode' => self::OFFICE_DOOR,
            ],
            187 => [
                'mode' => self::DOOR_OFFICE,
            ],
            231 => [
                'mode' => self::DOOR_DOOR,
            ],
            232 => [
                'mode' => self::DOOR_OFFICE,
            ],
            233 => [
                'mode' => self::OFFICE_DOOR,
            ],
            234 => [
                'mode' => self::OFFICE_OFFICE,
            ],
            291 => [
                'mode' => self::OFFICE_OFFICE,
            ],
            293 => [
                'mode' => self::DOOR_DOOR,
            ],
            294 => [
                'mode' => self::OFFICE_DOOR,
            ],
            295 => [
                'mode' => self::DOOR_OFFICE,
            ],
            366 => [
                'mode' => self::DOOR_PICKUP,
            ],
            368 => [
                'mode' => self::OFFICE_PICKUP,
            ],
            376 => [
                'mode' => self::DOOR_PICKUP,
            ],
            378 => [
                'mode' => self::OFFICE_PICKUP,
            ],
            480 => [
                'mode' => self::DOOR_DOOR,
            ],
            481 => [
                'mode' => self::DOOR_OFFICE,
            ],
            482 => [
                'mode' => self::OFFICE_DOOR,
            ],
            483 => [
                'mode' => self::OFFICE_OFFICE,
            ],
            485 => [
                'mode' => self::DOOR_PICKUP,
            ],
            486 => [
                'mode' => self::OFFICE_PICKUP,
            ],
            497 => [
                'mode' => self::DOOR_PICKUP,
            ],
            498 => [
                'mode' => self::OFFICE_PICKUP,
            ],
        ];

    public function getDirectionByCode(int $code)
    {
        foreach ($this->data as $key => $tariffElement) {
            if ($tariffElement['code'] === $code) {
                return $this->data[$key]['to'];
            }
        }
        return '';
    }

    public static function isTariffToOffice(int $code): bool
    {
        if (!isset(self::TARIFF_DATA[$code])) {
            throw new RuntimeException('Unknown tariff');
        }

        return self::TARIFF_DATA[$code]['mode'] === self::DOOR_OFFICE ||
               self::TARIFF_DATA[$code]['mode'] === self::OFFICE_OFFICE ||
               self::TARIFF_DATA[$code]['mode'] === self::PICKUP_OFFICE ||
               self::TARIFF_DATA[$code]['mode'] === self::PICKUP_PICKUP ||
               self::TARIFF_DATA[$code]['mode'] === self::OFFICE_PICKUP ||
               self::TARIFF_DATA[$code]['mode'] === self::DOOR_PICKUP;
    }

    public static function isTariffToDoor(int $code): bool
    {
        if (!isset(self::TARIFF_DATA[$code])) {
            throw new RuntimeException('Unknown tariff');
        }

        return self::TARIFF_DATA[$code]['mode'] === self::OFFICE_DOOR ||
               self::TARIFF_DATA[$code]['mode'] === self::DOOR_DOOR ||
               self::TARIFF_DATA[$code]['mode'] === self::PICKUP_DOOR;
    }

    public static function isTariffFromOffice(int $code): bool
    {
        if (!isset(self::TARIFF_DATA[$code])) {
            throw new RuntimeException('Unknown tariff');
        }

        return self::TARIFF_DATA[$code]['mode'] === self::OFFICE_DOOR ||
               self::TARIFF_DATA[$code]['mode'] === self::OFFICE_OFFICE ||
               self::TARIFF_DATA[$code]['mode'] === self::OFFICE_PICKUP;
    }

    public static function isTariffFromDoor(int $code): bool
    {
        if (!isset(self::TARIFF_DATA[$code])) {
            throw new RuntimeException('Unknown tariff');
        }

        return self::TARIFF_DATA[$code]['mode'] === self::DOOR_DOOR ||
               self::TARIFF_DATA[$code]['mode'] === self::DOOR_OFFICE ||
               self::TARIFF_DATA[$code]['mode'] === self::DOOR_PICKUP;
    }

    public static function getTariffList(): array
    {
        $registry = RegistrySingleton::getInstance();
        return array_combine(array_keys(self::TARIFF_DATA),
                             array_map(static fn(int $code, array $el) => sprintf('%s (%s)', $registry->get('language')->get(self::LOCALE_PREFIX . $code), $code),
                                 array_keys(self::TARIFF_DATA),
                                 self::TARIFF_DATA));
    }

}
