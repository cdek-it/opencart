<?php

require_once(DIR_SYSTEM . 'library/cdek_official/model/SettingsSeller.php');
require_once(DIR_SYSTEM . 'library/cdek_official/model/SettingsShipping.php');
require_once(DIR_SYSTEM . 'library/cdek_official/model/SettingsDimensions.php');
require_once(DIR_SYSTEM . 'library/cdek_official/model/SettingsPrice.php');
require_once(DIR_SYSTEM . 'library/cdek_official/model/SettingsAuth.php');

class Settings
{
    public SettingsAuth $authSettings;
    public SettingsSeller $sellerSettings;
    public SettingsShipping $shippingSettings;
    public SettingsDimensions $dimensionsSettings;
    public SettingsPrice $priceSettings;

    const settingsId = [
        'cdek_official_auth_id',
        'cdek_official_auth_secret',
        'cdek_official_auth__test_mode',
        'cdek_official_shipping_seller_name',
        'cdek_official_shipping_seller_phone',
        'cdek_official_seller_international_shipping_checkbox',
        'cdek_official_seller__true_seller_address',
        'cdek_official_seller__shipper',
        'cdek_official_seller__shipper_address',
        'cdek_official_seller__passport_series',
        'cdek_official_seller__passport_number',
        'cdek_official_seller__passport_issue_date',
        'cdek_official_seller__passport_issuing_authority',
        'cdek_official_seller__tin',
        'cdek_official_seller__date_of_birth',
        'cdek_official_shipping__tariff_name',
        'cdek_official_shipping__tariff_plug',
        'cdek_official_shipping__many_packages',
        'cdek_official_shipping__extra_days',
        'cdek_official_shipping__city',
        'cdek_official_shipping__city_address',
        'cdek_official_shipping__pvz',
        'cdek_official_dimensions__length',
        'cdek_official_dimensions__width',
        'cdek_official_dimensions__height',
        'cdek_official_dimensions__weight',
        'cdek_official_dimensions__use_default',
        'cdek_official_price__extra_price',
        'cdek_official_price__percentage_increase',
        'cdek_official_price__fix',
        'cdek_official_price__free',
        'cdek_official_price__insurance',
    ];

    public function __construct()
    {
        $this->authSettings = new SettingsAuth();
        $this->sellerSettings = new SettingsSeller();
        $this->shippingSettings = new SettingsShipping();
        $this->dimensionsSettings = new SettingsDimensions();
        $this->priceSettings = new SettingsPrice();
    }

    public function init($post)
    {
        $this->authSettings->init($post);
        $this->sellerSettings->init($post);
        $this->shippingSettings->init($post);
        $this->shippingSettings->setTariffs($post);
        $this->shippingSettings->setCurrency($post);
        $this->dimensionsSettings->init($post);
        $this->priceSettings->init($post);
    }

    /**
     * @throws Exception
     */
    public function validate()
    {
        $this->authSettings->validate();
        $this->sellerSettings->validate();
        $this->shippingSettings->validate();
        $this->dimensionsSettings->validate();
        $this->priceSettings->validate();
    }

    public function updateData(array &$data)
    {
        $data = array_merge(
            $data,
            $this->authSettings->toArray(),
            $this->sellerSettings->toArray(),
            $this->shippingSettings->toArray(),
            $this->dimensionsSettings->toArray(),
            $this->priceSettings->toArray(),
        );
    }
}