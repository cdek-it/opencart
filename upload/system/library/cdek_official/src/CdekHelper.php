<?php

namespace CDEK;

class CdekHelper
{
    public static function getLocality($locality)
    {
        $shippingOfficeJson = html_entity_decode($locality);
        return json_decode($shippingOfficeJson);
    }

    public static function checkLocalityAddress($locality): bool
    {
        if (is_object($locality) && ((!empty($locality->address) && is_string($locality->address)) ||
                                     (!empty($locality->country) && is_string($locality->country)) ||
                                     (!empty($locality->postal) && is_string($locality->postal)) ||
                                     (!empty($locality->city) && is_string($locality->city)))) {
            return true;
        } else {
            return false;
        }
    }

    public static function checkLocalityOffice($locality): bool
    {
        if (is_object($locality) && ((!empty($locality->code) && is_string($locality->code)) ||
                                     (!empty($locality->country) && is_string($locality->country)) ||
                                     (!empty($locality->postal) && is_string($locality->postal)) ||
                                     (!empty($locality->city) && is_string($locality->city)))) {
            return true;
        } else {
            return false;
        }
    }

    public static function hasLocalityCity($locality): bool
    {
        if (is_object($locality) && property_exists($locality, 'city') && is_string($locality->city)) {
            return true;
        }
        return false;
    }

    public static function hasLocalityCode($locality): bool
    {
        if (is_object($locality) && property_exists($locality, 'code') && is_string($locality->code)) {
            return true;
        }
        return false;
    }

}