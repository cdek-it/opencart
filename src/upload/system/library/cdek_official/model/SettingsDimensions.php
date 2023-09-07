<?php

require_once(DIR_SYSTEM . 'library/cdek_official/model/AbstractSettings.php');

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
        if ($this->dimensionsLength === '') {
            throw new Exception('cdek_error_dimensions_length_empty');
        }

        if ($this->dimensionsWidth === '') {
            throw new Exception('cdek_error_dimensions_width_empty');
        }

        if ($this->dimensionsHeight === '') {
            throw new Exception('cdek_error_dimensions_height_empty');
        }

        if ($this->dimensionsWeight === '') {
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