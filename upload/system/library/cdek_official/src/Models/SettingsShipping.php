<?php

namespace CDEK\Models;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;
use RuntimeException;

class SettingsShipping extends ValidatableSettingsContract
{
    protected const PARAM_ID
        = [
            'cdek_official_shipping__tariff_name'   => 'shippingTariffName',
            'cdek_official_shipping__tariff_plug'   => 'shippingTariffPlug',
            'cdek_official_shipping__many_packages' => 'shippingManyPackages',
            'cdek_official_shipping__extra_days'    => 'shippingExtraDays',
            'cdek_official_shipping__city'          => 'shippingCity',
            'cdek_official_shipping__city_code'     => 'shippingCityCode',
            'cdek_official_shipping__city_address'  => 'shippingCityAddress',
            'cdek_official_shipping__pvz'           => 'shippingPvz',
            'cdek_official_shipping__pvz_code'      => 'shippingPvzCode',
        ];
    public array $enabledTariffs;
    public array $shippingCurrencies;
    public $shippingTariffName;
    public $shippingTariffPlug;
    public $shippingManyPackages;
    public $shippingExtraDays;
    public $shippingCity;
    public $shippingCityCode;
    public $shippingCityAddress;
    public $shippingPvz;
    public $shippingPvzCode;
    public Currency $currency;

    public function __construct()
    {
        $this->currency = new Currency;
        $this->enabledTariffs = [];
    }

    /**
     * @throws Exception
     */
    final public function validate(): void
    {
        if (empty($this->enabledTariffs)) {
            throw new RuntimeException('cdek_error_shipping_tariffs_empty');
        }
    }

    public function init(array $post){
        $this->setTariffs($post);
        parent::init($post);
    }

    final public function setTariffs(array $input): void
    {
        foreach ($input as $key => $value) {
            if ($value && preg_match('/^cdek_official_shipping_tariff_\d+$/', $key)) {
                var_dump($key);
                $this->enabledTariffs[] = $key;
            }
        }
    }

    public function setCurrency($post)
    {
        if (isset($post['cdek_official_shipping__currency'])) {
            $currency = $post['cdek_official_shipping__currency'];
            $this->currency->selectCurrency((int)$currency);
        }
        $this->shippingCurrencies = $this->currency->getCurrency();
    }
}
