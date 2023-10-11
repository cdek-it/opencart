<?php

namespace CDEK\model;

use Exception;

class SettingsPrice extends AbstractSettings
{
    public $priceExtraPrice;
    public $pricePercentageIncrease;
    public $priceFix;
    public $priceFree;
    public $priceInsurance;

    const PARAM_ID = [
        'cdek_official_price__extra_price' => 'priceExtraPrice',
        'cdek_official_price__percentage_increase' => 'pricePercentageIncrease',
        'cdek_official_price__fix' => 'priceFix',
        'cdek_official_price__free' => 'priceFree',
        'cdek_official_price__insurance' => 'priceInsurance',
    ];

    /**
     * @throws Exception
     */
    public function validate()
    {
        if ((float)$this->priceExtraPrice < 0) {
            throw new Exception('cdek_error_price_extra_price_invalid');
        }

        if ((float)$this->pricePercentageIncrease < 0) {
            throw new Exception('cdek_error_price_percentage_increase_invalid');
        }

        if ((float)$this->priceFix < 0) {
            throw new Exception('cdek_error_price_fix_invalid');
        }

        if ((float)$this->priceFree < 0) {
            throw new Exception('cdek_error_price_free_invalid');
        }
    }
}