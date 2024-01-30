<?php

namespace CDEK\Models;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;
use RuntimeException;

class SettingsPrice extends ValidatableSettingsContract
{
    public float $priceExtraPrice = 0;
    public float $pricePercentageIncrease = 0;
    public float $priceFix = 0;
    public float $priceFree = 0;

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
