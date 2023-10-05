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