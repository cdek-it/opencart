<?php

namespace CDEK\Helpers;

class LocationHelper
{
    public static function getLocality(string $locality): array
    {
        $shippingOfficeJson = html_entity_decode($locality);
        return json_decode($shippingOfficeJson, true, 512, JSON_THROW_ON_ERROR);
    }
}
