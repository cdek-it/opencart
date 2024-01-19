<?php

namespace CDEK\Models;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;
use RuntimeException;

class SettingsDimensions extends ValidatableSettingsContract
{
    const PARAM_ID
        = [
            'cdek_official_dimensions__length'      => 'dimensionsLength',
            'cdek_official_dimensions__width'       => 'dimensionsWidth',
            'cdek_official_dimensions__height'      => 'dimensionsHeight',
            'cdek_official_dimensions__weight'      => 'dimensionsWeight',
            'cdek_official_dimensions__use_default' => 'dimensionsUseDefault',
        ];
    public $dimensionsLength;
    public $dimensionsWidth;
    public $dimensionsHeight;
    public $dimensionsWeight;
    public $dimensionsUseDefault;

    /**
     * @throws Exception
     */
    public function validate(): void
    {
        if (empty($this->dimensionsLength)) {
            throw new RuntimeException('cdek_error_dimensions_length_empty');
        }

        if (empty($this->dimensionsWidth)) {
            throw new RuntimeException('cdek_error_dimensions_width_empty');
        }

        if (empty($this->dimensionsHeight)) {
            throw new RuntimeException('cdek_error_dimensions_height_empty');
        }

        if (empty($this->dimensionsWeight)) {
            throw new RuntimeException('cdek_error_dimensions_weight_empty');
        }

        if (!is_numeric($this->dimensionsLength)) {
            throw new RuntimeException('cdek_error_dimensions_length_invalid');
        }

        if (!is_numeric($this->dimensionsWidth)) {
            throw new RuntimeException('cdek_error_dimensions_width_invalid');
        }

        if (!is_numeric($this->dimensionsHeight)) {
            throw new RuntimeException('cdek_error_dimensions_height_invalid');
        }

        if (!is_numeric($this->dimensionsWeight)) {
            throw new RuntimeException('cdek_error_dimensions_weight_invalid');
        }
    }

    public function getParams()
    {
        return [
            'length' => $this->dimensionsLength,
            'width'  => $this->dimensionsWidth,
            'height' => $this->dimensionsHeight,
            'weight' => $this->dimensionsWeight,
        ];
    }
}
