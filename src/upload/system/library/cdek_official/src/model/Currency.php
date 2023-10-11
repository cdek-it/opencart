<?php

namespace CDEK\model;

class Currency
{
    private array $data = [
        [
            'code' => 1,
            'key' => 'cdek_shipping__currency_rub',
            'select' => true
        ],
        [
            'code' => 3,
            'key' => 'cdek_shipping__currency_usd',
            'select' => false
        ],
        [
            'code' => 4,
            'key' => 'cdek_shipping__currency_eur',
            'select' => false
        ],
    ];

    public function getCurrency(): array
    {
        return $this->data;
    }

    public function selectCurrency(int $code): void
    {
        for ($i = 0; $i < count($this->data); $i++) {
            $this->data[$i]['select'] = false;
            if ($this->data[$i]['code'] === $code) {
                $this->data[$i]['select'] = true;
            }
        }
    }

    public function getSelectedCurrency(): string
    {
        foreach ($this->data as $currency) {
            if ($currency['select']) {
                return (string) $currency['code'];
            }
        }
        return '1';
    }
}