<?php

require_once(DIR_SYSTEM . 'library/cdek_official/model/Tariffs.php');
require_once(DIR_SYSTEM . 'library/cdek_official/model/AbstractSettings.php');

class SettingsShipping extends AbstractSettings
{
    public $shippingTariffs;
    public $shippingTariffName;
    public $shippingTariffPlug;
    public $shippingManyPackages;
    public $shippingExtraDays;
    public $shippingCity;
    public $shippingCityCode;
    public $shippingCityAddress;
    public $shippingPvz;

    const PARAM_ID = [
        'cdek_official_shipping__tariff_name' => 'shippingTariffName',
        'cdek_official_shipping__tariff_plug' => 'shippingTariffPlug',
        'cdek_official_shipping__many_packages' => 'shippingManyPackages',
        'cdek_official_shipping__extra_days' => 'shippingExtraDays',
        'cdek_official_shipping__city' => 'shippingCity',
        'cdek_official_shipping__city_code' => 'shippingCityCode',
        'cdek_official_shipping__city_address' => 'shippingCityAddress',
        'cdek_official_shipping__pvz' => 'shippingPvz',
    ];

    protected $tariffs;

    public function __construct()
    {
        $this->tariffs = new Tariffs();
    }

    /**
     * @throws Exception
     */
    public function validate()
    {
        if ($this->isTariffsEmpty()) {
            throw new Exception('cdek_error_shipping_tariffs_empty');
        }

        if (empty($this->shippingCity)) {
            throw new Exception('cdek_error_shipping_city_empty');
        }

        if (empty($this->shippingCityAddress)) {
            throw new Exception('cdek_error_shipping_city_address_empty');
        }

        if (empty($this->shippingPvz)) {
            throw new Exception('cdek_error_shipping_pvz_empty');
        }
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
}