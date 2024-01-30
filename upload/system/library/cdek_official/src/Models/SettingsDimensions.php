<?php

namespace CDEK\Models;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;
use RuntimeException;

class SettingsDimensions extends ValidatableSettingsContract
{
    public int $dimensionsLength = 0;
    public int $dimensionsWidth = 0;
    public int $dimensionsHeight = 0;
    public int $dimensionsWeight = 0;

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
