<?php

namespace CDEK\model;

use Exception;

class SettingsShipping extends AbstractSettings
{
    public $shippingTariffs;
    public array $shippingCurrencies;
    public $shippingTariffName;
    public $shippingTariffPlug;
    public $shippingManyPackages;
    public $shippingExtraDays;
    public $shippingCity;
    public $shippingCityCode;
    public $shippingCityAddress;
    public $shippingSenderLocality;
    public $shippingPvz;
    public $shippingPvzCode;
    public $tariffs;
    public Currency $currency;

    const PARAM_ID = [
        'cdek_official_shipping__tariff_name' => 'shippingTariffName',
        'cdek_official_shipping__tariff_plug' => 'shippingTariffPlug',
        'cdek_official_shipping__many_packages' => 'shippingManyPackages',
        'cdek_official_shipping__extra_days' => 'shippingExtraDays',
        'cdek_official_shipping__city' => 'shippingCity',
        'cdek_official_shipping__city_code' => 'shippingCityCode',
        'cdek_official_shipping__city_address' => 'shippingCityAddress',
        'cdek_official_sender_locality' => 'shippingSenderLocality',
        'cdek_official_shipping__pvz' => 'shippingPvz',
        'cdek_official_shipping__pvz_code' => 'shippingPvzCode',
    ];

    public function __construct()
    {
        $this->tariffs = new Tariffs();
        $this->currency = new Currency();
    }

    /**
     * @throws Exception
     */
    public function validate()
    {
        if ($this->isTariffsEmpty()) {
            throw new Exception('cdek_error_shipping_tariffs_empty');
        }

//        if (empty($this->shippingCity)) {
//            throw new Exception('cdek_error_shipping_city_empty');
//        }
//
        if (empty($this->shippingSenderLocality)) {
            throw new Exception('cdek_error_shipping_sender_locality');
        }
//
//        if (empty($this->shippingPvz)) {
//            throw new Exception('cdek_error_shipping_pvz_empty');
//        }
    }

    public function setTariffs($post)
    {
        $tariffsChecked = [];
        foreach ($post as $key => $value) {
            if (preg_match('/^cdek_official_shipping_tariff_\d+$/', $key)) {
                $tariffsChecked[$key] = $value;
            }
        }

        foreach ($this->tariffs->data as $tariffElem) {
            if (in_array($tariffElem['code'], $tariffsChecked)) {
                $this->tariffs->setStatusByCode($tariffElem['code'], true);
                continue;
            }
            $this->tariffs->setStatusByCode($tariffElem['code'], false);
        }

        $this->shippingTariffs = $this->tariffs->data;
    }

    protected function isTariffsEmpty()
    {
        foreach ($this->tariffs->data as $elem) {
            if ($elem['enable']) {
                return false;
            }
        }
        return true;
    }

    public function setCurrency($post)
    {
        if (isset($post['cdek_official_shipping__currency'])) {
            $currency = $post['cdek_official_shipping__currency'];
            $this->currency->selectCurrency((int) $currency);
        }
        $this->shippingCurrencies = $this->currency->getCurrency();
    }
}