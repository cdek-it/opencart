<?php

namespace CDEK;

use CDEK\Models\Tariffs;

class CdekHelper
{
    public static function getLocality(string $locality): array
    {
        $shippingOfficeJson = html_entity_decode($locality);
        return json_decode($shippingOfficeJson, true, 512, JSON_THROW_ON_ERROR);
    }

    public static function checkLocalityAddress(array $locality): bool
    {
        return ((!empty($locality['address']) && is_string($locality['address'])) ||
                (!empty($locality['country']) && is_string($locality['country'])) ||
                (!empty($locality['postal']) && is_string($locality['postal'])) ||
                (!empty($locality['city']) && is_string($locality['city'])));
    }

    public static function hasLocalityCity($locality): bool
    {
        return is_object($locality) && property_exists($locality, 'city') && is_string($locality->city);
    }

    public static function hasLocalityCode($locality): bool
    {
        return is_object($locality) && property_exists($locality, 'code') && is_string($locality->code);
    }

    public static function getTariffDirectionByOrderId($modelSaleOrder, $orderId)
    {
        $orderOC         = $modelSaleOrder->getOrder($orderId);
        $tariffNameParts = explode('_', $orderOC['shipping_code']);
        $tariffCode      = end($tariffNameParts);
        $tariffMode      = new Tariffs;
        return $tariffMode->getDirectionByCode((int)$tariffCode);
    }
}
