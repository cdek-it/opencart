<?php

namespace CDEK;

use CDEK\Models\SettingsAuth;
use CDEK\Models\SettingsDimensions;
use CDEK\Models\SettingsPrice;
use CDEK\Models\SettingsSeller;
use CDEK\Models\SettingsShipping;
use Exception;
use ModelSettingSetting;

class SettingsSingleton
{
    private static SettingsSingleton $instance;
    public SettingsAuth $authSettings;
    public SettingsSeller $sellerSettings;
    public SettingsShipping $shippingSettings;
    public SettingsDimensions $dimensionsSettings;
    public SettingsPrice $priceSettings;

    public function __construct(array $data = [])
    {
        if (empty($data)) {
            $registry = RegistrySingleton::getInstance();
            $registry->get('load')->model('setting/setting');

            /** @var ModelSettingSetting $settingsModel */
            $settingsModel = $registry->get('model_setting_setting');
            $data          = $settingsModel->getSetting('cdek_official');
        }

        $this->authSettings       = new SettingsAuth($data);
        $this->sellerSettings     = new SettingsSeller($data);
        $this->shippingSettings   = new SettingsShipping($data);
        $this->dimensionsSettings = new SettingsDimensions($data);
        $this->priceSettings      = new SettingsPrice($data);
    }

    public static function getInstance(array $data = []): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self;
        }
        if(!empty($data)){
            self::$instance = new self($data);
        }
        return self::$instance;
    }

    public function save(): void
    {
        $registry = RegistrySingleton::getInstance();
        $registry->get('load')->model('setting/setting');

        /** @var ModelSettingSetting $settingsModel */
        $settingsModel = $registry->get('model_setting_setting');

        $settingsModel->editSetting('cdek_official', $this->__serialize());
    }

    /**
     * @throws Exception
     */
    final public function validate(): void
    {
        $this->authSettings->validate();
        $this->sellerSettings->validate();
        $this->shippingSettings->validate();
        $this->dimensionsSettings->validate();
        $this->priceSettings->validate();
    }

    final public function __serialize(): array
    {
        return array_merge($this->authSettings->__serialize(),
                           $this->sellerSettings->__serialize(),
                           $this->shippingSettings->__serialize(),
                           $this->dimensionsSettings->__serialize(),
                           $this->priceSettings->__serialize());
    }
}
