<?php

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
        ]
    ];

    public function setStatusByCode($code, $status)
    {
        foreach ($this->data as $key => $tariffElement) {
            if ($tariffElement['code'] === $code) {
                $this->data[$key]['enable'] = $status;
            }
        }
    }
}