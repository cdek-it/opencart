<?php

namespace CDEK\Models\Settings;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;
use RuntimeException;

class SettingsShipping extends ValidatableSettingsContract
{
    public array $enabledTariffs = [];
    public int $shippingCurrency = 1;
    public int $shippingExtraDays = 0;
    public string $shippingCityAddress = '';
    public string $shippingPvz = '';

    /**
     * @throws Exception
     */
    final public function validate(): void
    {
        if (empty($this->enabledTariffs)) {
            throw new RuntimeException('cdek_error_shipping_tariffs_empty');
        }
    }

    public function __construct(array $data = null)
    {
        if($data !== null){
            $this->setTariffs($data);
        }
        parent::__construct($data);
    }

    final public function setTariffs(array $input): void
    {
        foreach ($input as $key => $value) {
            if ($value && preg_match('/^cdek_official_shipping_tariff_\d+$/', $key)) {
                $this->enabledTariffs[] = $value;
            }
        }
    }
}
