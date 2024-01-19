<?php

namespace CDEK;

use CDEK\Models\Tariffs;

class CdekHelper
{
    public static function getLocality($locality)
    {
        $shippingOfficeJson = html_entity_decode($locality);
        return json_decode($shippingOfficeJson);
    }

    public static function checkLocalityAddress($locality): bool
    {
        return is_object($locality) &&
               ((!empty($locality->address) && is_string($locality->address)) ||
                (!empty($locality->country) && is_string($locality->country)) ||
                (!empty($locality->postal) && is_string($locality->postal)) ||
                (!empty($locality->city) && is_string($locality->city)));
    }

    public static function checkLocalityOffice($locality): bool
    {
        return is_object($locality) &&
               ((!empty($locality->code) && is_string($locality->code)) ||
                (!empty($locality->country) && is_string($locality->country)) ||
                (!empty($locality->postal) && is_string($locality->postal)) ||
                (!empty($locality->city) && is_string($locality->city)));
    }

    public static function hasLocalityCity($locality): bool
    {
        return is_object($locality) && property_exists($locality, 'city') && is_string($locality->city);
    }

    public static function hasLocalityCode($locality): bool
    {
        return is_object($locality) && property_exists($locality, 'code') && is_string($locality->code);
    }

    public static function calculateRecomendedPackage($productsPackages, $defaultPackages)
    {
        $lengthList = [];
        $widthList  = [];
        $heightList = [];

        $weightTotal = 0;
        foreach ($productsPackages as $product) {
            $weight = $product['weight'];
            if ($weight === 0) {
                $weight = $defaultPackages['weight'];
            }
            $weightTotal += $product['quantity'] * $weight;

            $packageProduct = [$product['length'], $product['width'], $product['height']];
            sort($packageProduct);

            if ($product['quantity'] > 1) {
                $packageProduct[0] = $product['quantity'] * $packageProduct[0];
                sort($packageProduct);
            }

            $lengthList[] = $packageProduct[0];
            $heightList[] = $packageProduct[1];
            $widthList[]  = $packageProduct[2];
        }

        unset($defaultPackages['weight']);
        sort($defaultPackages);
        $lengthList[] = (int)$defaultPackages[0];
        $widthList[]  = (int)$defaultPackages[1];
        $heightList[] = (int)$defaultPackages[2];

        rsort($lengthList);
        rsort($widthList);
        rsort($heightList);

        return [
            'length' => $lengthList[0],
            'width'  => $widthList[0],
            'height' => $heightList[0],
            'weight' => $weightTotal
        ];
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
