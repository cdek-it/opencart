<?php

namespace CDEK\Models;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;
use RuntimeException;

class SettingsPrice extends ValidatableSettingsContract
{
    const PARAM_ID
        = [
            'cdek_official_price__extra_price'         => 'priceExtraPrice',
            'cdek_official_price__percentage_increase' => 'pricePercentageIncrease',
            'cdek_official_price__fix'                 => 'priceFix',
            'cdek_official_price__free'                => 'priceFree',
            'cdek_official_price__insurance'           => 'priceInsurance',
        ];
    public $priceExtraPrice;
    public $pricePercentageIncrease;
    public $priceFix;
    public $priceFree;
    public $priceInsurance;

    /**
     * @throws Exception
     */
    public function validate(): void
    {
        if ((float)$this->priceExtraPrice < 0) {
            throw new RuntimeException('cdek_error_price_extra_price_invalid');
        }

        if ((float)$this->pricePercentageIncrease < 0) {
            throw new RuntimeException('cdek_error_price_percentage_increase_invalid');
        }

        if ((float)$this->priceFix < 0) {
            throw new RuntimeException('cdek_error_price_fix_invalid');
        }

        if ((float)$this->priceFree < 0) {
            throw new RuntimeException('cdek_error_price_free_invalid');
        }
    }
}
