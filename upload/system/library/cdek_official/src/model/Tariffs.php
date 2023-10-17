<?php

namespace CDEK\model;

class Tariffs
{
    public $data = [
        [
            'key' => 'cdek_shipping__tariff_name_136',
            'from' => 'store',
            'to' => 'store',
            'code' => 136,
            'enable' => true
        ],
        [
            'key' => 'cdek_shipping__tariff_name_137',
            'from' => 'store',
            'to' => 'door',
            'code' => 137,
            'enable' => true
        ],
        [
            'key' => 'cdek_shipping__tariff_name_138',
            'from' => 'door',
            'to' => 'store',
            'code' => 138,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_139',
            'from' => 'door',
            'to' => 'door',
            'code' => 139,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_366',
            'from' => 'door',
            'to' => 'postamat',
            'code' => 366,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_368',
            'from' => 'store',
            'to' => 'postamat',
            'code' => 368,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_184',
            'from' => 'door',
            'to' => 'door',
            'code' => 184,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_185',
            'from' => 'store',
            'to' => 'store',
            'code' => 185,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_186',
            'from' => 'store',
            'to' => 'door',
            'code' => 186,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_187',
            'from' => 'door',
            'to' => 'store',
            'code' => 187,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_231',
            'from' => 'door',
            'to' => 'door',
            'code' => 231,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_232',
            'from' => 'door',
            'to' => 'store',
            'code' => 232,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_233',
            'from' => 'store',
            'to' => 'door',
            'code' => 233,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_234',
            'from' => 'store',
            'to' => 'store',
            'code' => 234,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_291',
            'from' => 'store',
            'to' => 'store',
            'code' => 291,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_293',
            'from' => 'door',
            'to' => 'door',
            'code' => 293,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_294',
            'from' => 'store',
            'to' => 'door',
            'code' => 294,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_295',
            'from' => 'door',
            'to' => 'store',
            'code' => 295,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_361',
            'from' => 'door',
            'to' => 'postamat',
            'code' => 361,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_363',
            'from' => 'store',
            'to' => 'postamat',
            'code' => 363,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_376',
            'from' => 'door',
            'to' => 'postamat',
            'code' => 376,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_378',
            'from' => 'store',
            'to' => 'postamat',
            'code' => 378,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_480',
            'from' => 'door',
            'to' => 'door',
            'code' => 480,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_481',
            'from' => 'door',
            'to' => 'store',
            'code' => 481,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_482',
            'from' => 'store',
            'to' => 'door',
            'code' => 482,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_483',
            'from' => 'store',
            'to' => 'store',
            'code' => 483,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_485',
            'from' => 'door',
            'to' => 'postamat',
            'code' => 485,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_486',
            'from' => 'store',
            'to' => 'postamat',
            'code' => 486,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_497',
            'from' => 'door',
            'to' => 'postamat',
            'code' => 497,
            'enable' => false
        ],
        [
            'key' => 'cdek_shipping__tariff_name_498',
            'from' => 'store',
            'to' => 'postamat',
            'code' => 498,
            'enable' => false
        ],
    ];


    public function setStatusByCode($code, $status)
    {
        foreach ($this->data as $key => $tariffElement) {
            if ($tariffElement['code'] === $code) {
                $this->data[$key]['enable'] = $status;
            }
        }
    }

    public function getDirectionByCode($code)
    {
        foreach ($this->data as $key => $tariffElement) {
            if ($tariffElement['code'] === $code) {
                return $this->data[$key]['to'];
            }
        }
        return '';
    }

    public function getFromByCode($code)
    {
        foreach ($this->data as $key => $tariffElement) {
            if ($tariffElement['code'] === $code) {
                return $this->data[$key]['from'];
            }
        }
        return '';
    }
}