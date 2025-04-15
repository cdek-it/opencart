<?php

namespace CDEK\Helpers;

use JsonException;

class LocationHelper
{
    /**
     * @throws JsonException
     */
    public static function getLocality(string $locality): array
    {
        $shippingOfficeJson = html_entity_decode($locality);
        return json_decode($shippingOfficeJson, true, 512, JSON_THROW_ON_ERROR);
    }
}
