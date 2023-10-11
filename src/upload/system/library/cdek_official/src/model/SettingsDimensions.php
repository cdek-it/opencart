<?php

namespace CDEK\model;

use Exception;

class SettingsDimensions extends AbstractSettings
{
    public $dimensionsLength;
    public $dimensionsWidth;
    public $dimensionsHeight;
    public $dimensionsWeight;
    public $dimensionsUseDefault;

    const PARAM_ID = [
        'cdek_official_dimensions__length' => 'dimensionsLength',
        'cdek_official_dimensions__width' => 'dimensionsWidth',
        'cdek_official_dimensions__height' => 'dimensionsHeight',
        'cdek_official_dimensions__weight' => 'dimensionsWeight',
        'cdek_official_dimensions__use_default' => 'dimensionsUseDefault',
    ];

    /**
     * @throws Exception
     */
    public function validate()
    {
        if (empty($this->dimensionsLength)) {
            throw new Exception('cdek_error_dimensions_length_empty');
        }

        if (empty($this->dimensionsWidth)) {
            throw new Exception('cdek_error_dimensions_width_empty');
        }

        if (empty($this->dimensionsHeight)) {
            throw new Exception('cdek_error_dimensions_height_empty');
        }

        if (empty($this->dimensionsWeight)) {
            throw new Exception('cdek_error_dimensions_weight_empty');
        }

        if (!is_numeric($this->dimensionsLength)) {
            throw new Exception('cdek_error_dimensions_length_invalid');
        }

        if (!is_numeric($this->dimensionsWidth)) {
            throw new Exception('cdek_error_dimensions_width_invalid');
        }

        if (!is_numeric($this->dimensionsHeight)) {
            throw new Exception('cdek_error_dimensions_height_invalid');
        }

        if (!is_numeric($this->dimensionsWeight)) {
            throw new Exception('cdek_error_dimensions_weight_invalid');
        }
    }
}