<?php

namespace CDEK;

use CDEK\Models\SettingsAuth;
use CDEK\Models\SettingsDimensions;
use CDEK\Models\SettingsPrice;
use CDEK\Models\SettingsSeller;
use CDEK\Models\SettingsShipping;
use Exception;

class Settings
{
    public SettingsAuth $authSettings;
    public SettingsSeller $sellerSettings;
    public SettingsShipping $shippingSettings;
    public SettingsDimensions $dimensionsSettings;
    public SettingsPrice $priceSettings;

    public function __construct()
    {
        $this->authSettings       = new SettingsAuth;
        $this->sellerSettings     = new SettingsSeller;
        $this->shippingSettings   = new SettingsShipping;
        $this->dimensionsSettings = new SettingsDimensions;
        $this->priceSettings      = new SettingsPrice;
    }

    public function init($post): void
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
    public function validate(): void
    {
        $this->authSettings->validate();
        $this->sellerSettings->validate();
        $this->shippingSettings->validate();
        $this->dimensionsSettings->validate();
        $this->priceSettings->validate();
    }

    public function updateData(array &$data): void
    {
        $data = array_merge($data,
                            $this->authSettings->toArray(),
                            $this->sellerSettings->toArray(),
                            $this->shippingSettings->toArray(),
                            $this->dimensionsSettings->toArray(),
                            $this->priceSettings->toArray(),);
    }
}
