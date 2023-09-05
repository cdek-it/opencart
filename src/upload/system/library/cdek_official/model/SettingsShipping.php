<?php

require_once(DIR_SYSTEM . 'library/cdek_official/model/AbstractSettings.php');

class SettingsShipping extends AbstractSettings
{
    public $shippingTariffName;
    public $shippingTariffPlug;
    public $shippingManyPackages;
    public $shippingExtraDays;
    public $shippingCity;
    public $shippingCityAddress;
    public $shippingPvz;

    const PARAM_ID = [
        'cdek_official_shipping__tariff_name' => 'shippingTariffName',
        'cdek_official_shipping__tariff_plug' => 'shippingTariffPlug',
        'cdek_official_shipping__many_packages' => 'shippingManyPackages',
        'cdek_official_shipping__extra_days' => 'shippingExtraDays',
        'cdek_official_shipping__city' => 'shippingCity',
        'cdek_official_shipping__city_address' => 'shippingCityAddress',
        'cdek_official_shipping__pvz' => 'shippingPvz',
    ];

    /**
     * @throws Exception
     */
    public function validate()
    {
        if ($this->shippingTariffName === '') {
            throw new Exception('cdek_error_shipping_tariff_name_empty');
        }

        if ($this->shippingTariffPlug === '') {
            throw new Exception('cdek_error_shipping_tariff_plug_empty');
        }

        if ($this->shippingManyPackages === '') {
            throw new Exception('cdek_error_shipping_many_packages_empty');
        }

        if ($this->shippingExtraDays === '') {
            throw new Exception('cdek_error_shipping_extra_days_empty');
        }

        if ($this->shippingCity === '') {
            throw new Exception('cdek_error_shipping_city_empty');
        }

        if ($this->shippingCityAddress === '') {
            throw new Exception('cdek_error_shipping_city_address_empty');
        }

        if ($this->shippingPvz === '') {
            throw new Exception('cdek_error_shipping_pvz_empty');
        }
    }
}