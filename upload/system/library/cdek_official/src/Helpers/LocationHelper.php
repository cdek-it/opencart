<?php

namespace CDEK\Helpers;

use CDEK\Exceptions\DecodeException;
use JsonException;

class LocationHelper
{
    /**
     * @throws DecodeException
     */
    public static function getLocality(string $locality): array
    {
        $shippingOfficeJson = html_entity_decode($locality);

        try {
            return json_decode($shippingOfficeJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new DecodeException('Failed to decode JSON: ' . $e->getMessage(), 0, $e);
        }
    }
}
