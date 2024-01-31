<?php

namespace CDEK\Helpers;

use Cart\Weight;
use CDEK\CdekApi;
use CDEK\CdekHelper;
use CDEK\Models\Tariffs;
use CDEK\RegistrySingleton;
use CDEK\SettingsSingleton;

class DeliveryCalculator
{
    public static function getQuoteForAddress(array $deliveryAddress): ?array
    {
        $registry     = RegistrySingleton::getInstance();
        $registry->get('load')->language('extension/shipping/cdek_official');
        $cartProducts = $registry->get('cart')->getProducts();
        $weight       = $registry->get('weight');
        if (empty($cartProducts) && empty($weight)) {
            return null;
        }

        return [
            'code'       => 'cdek_official',
            'title'      => $registry->get('language')->get('text_title'),
            'quote'      => self::calcuateQuote($deliveryAddress),
            'sort_order' => $registry->get('config')->get('shipping_cdek_official_sort_order'),
            'error'      => false,
        ];
    }

    private static function calcuateQuote(array $deliveryAddress): array
    {
        $settings = SettingsSingleton::getInstance();
        $recipientLocation = CdekApi::getCityByParam(trim($deliveryAddress['city'] ?? ''),
                                                            trim($deliveryAddress['postcode'] ?? ''));
        if (empty($recipientLocation)) {
            return [];
        }

        $tariffCalculated      = [];
        $recommendedDimensions = self::getPackage();

        if (empty($recommendedDimensions)) {
            return [];
        }

        $toLocationCode   = $recipientLocation[0]['code'];
        if (!empty($settings->shippingSettings->shippingCityAddress)) {
            $locality = CdekHelper::getLocality($settings->shippingSettings->shippingCityAddress);
            $data     = [
                'currency'      => $settings->shippingSettings->shippingCurrency,
                'from_location' => [
                    'address'      => $locality['address'] ?? '',
                    'country_code' => $locality['country'] ?? '',
                    'postal_code'  => $locality['postal'] ?? '',
                    'city'         => $locality['city'] ?? '',
                ],
                'to_location'   => [
                    'code' => $toLocationCode,
                ],
                'packages'      => $recommendedDimensions,
            ];
            $result   = CdekApi::calculate($data);
            if (!empty($result) && isset($result['tariff_codes'])) {
                foreach ($result['tariff_codes'] as $tariff) {
                    if (!in_array((string)$tariff['tariff_code'], $settings->shippingSettings->enabledTariffs, true) ||
                        Tariffs::isTariffFromDoor($tariff['tariff_code'])) {
                        continue;
                    }

                    $prefix = Tariffs::isTariffToDoor($tariff['tariff_code']) ? 'door' : 'office';

                    $tariffCalculated["{$prefix}_{$tariff['tariff_code']}"] = self::formatQuoteData($tariff);
                }
            }
        }

        if (!empty($settings->shippingSettings->shippingPvz)) {
            $locality = CdekHelper::getLocality($settings->shippingSettings->shippingPvz);
            if (CdekHelper::checkLocalityOffice($locality)) {
                $data   = [
                    'currency'      => $settings->shippingSettings->shippingCurrency,
                    'from_location' => [
                        'country_code' => $locality['country'] ?? '',
                        'postal_code'  => $locality['postal'] ?? '',
                        'city'         => $locality['city'] ?? '',
                    ],
                    'to_location'   => [
                        'code' => $toLocationCode,
                    ],
                    'packages'      => $recommendedDimensions,
                ];
                $result = CdekApi::calculate($data);
                foreach ($result['tariff_codes'] as $tariff) {
                    if (!in_array((string)$tariff['tariff_code'], $settings->shippingSettings->enabledTariffs, true) ||
                        Tariffs::isTariffFromOffice($tariff['tariff_code'])) {
                        continue;
                    }

                    $prefix = Tariffs::isTariffToDoor($tariff['tariff_code']) ? 'door' : 'office';

                    $tariffCalculated["{$prefix}_{$tariff['tariff_code']}"] = self::formatQuoteData($tariff);
                }
            }
        }

        return $tariffCalculated;
    }

    public static function getRecommendedPackage(array $packages): array
    {
        $settings = SettingsSingleton::getInstance();
        $defaultPackages = [
            $settings->dimensionsSettings->dimensionsLength,
            $settings->dimensionsSettings->dimensionsWidth,
            $settings->dimensionsSettings->dimensionsHeight,
        ];
        $lengthList = [];
        $widthList  = [];
        $heightList = [];

        $weightTotal = 0;
        foreach ($packages as $product) {
            $weight = $product['weight'];
            if ($weight === 0) {
                $weight = $settings->dimensionsSettings->dimensionsWeight;
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
            'weight' => $weightTotal,
        ];
    }

    private static function getPackage(): array
    {
        $cartProducts = RegistrySingleton::getInstance()->get('cart')->getProducts();
        $packages = [];
        foreach ($cartProducts as $product) {
            if ((int)$product['tax_class_id'] !== 10) {
                $dimensions = self::getDimensions($product);
                $packages[] = $dimensions;
            }
        }

        return self::getRecommendedPackage($packages);
    }

    private static function getDimensions(array $product): array
    {
        $settings = SettingsSingleton::getInstance();
        $registry = RegistrySingleton::getInstance();
        return [
            'height'   => ((int)$product['height']) ?: $settings->dimensionsSettings->dimensionsHeight,
            'length'   => ((int)$product['length']) ?: $settings->dimensionsSettings->dimensionsLength,
            'weight'   => ((int)((new Weight($registry))->convert($product['weight'], $product['weight_class_id'],
                                                                     '2')) /
                           (int)$product['quantity']) ?: $settings->dimensionsSettings->dimensionsWeight,
            'width'    => ((int)$product['width']) ?: $settings->dimensionsSettings->dimensionsWidth,
            'quantity' => (int)$product['quantity'],
        ];
    }

    private static function formatQuoteData(array $tariff): array
    {
        $registry = RegistrySingleton::getInstance();

        $title = $registry->get('language')->get("cdek_shipping__tariff_name_{$tariff['tariff_code']}") .
                 self::getPeriod($tariff);
        $total = self::getTotalSum($tariff);

        return [
            'code'         => 'cdek_official.' .
                              (Tariffs::isTariffToDoor($tariff['tariff_code']) ? 'door_' : 'office_') .
                              $tariff['tariff_code'],
            'title'        => $registry->get('language')->get('text_title') . ': ' . $title,
            'cost'         => $total,
            'tax_class_id' => $tariff['tariff_code'],
            'text'         => $registry->get('currency')->format($total,
                                                                 $registry->get('session')->data['currency']),
        ];
    }

    private static function getPeriod(array $tariff): string
    {
        $settings = SettingsSingleton::getInstance();
        $registry = RegistrySingleton::getInstance();
        $extraDays = $settings->shippingSettings->shippingExtraDays;
        $min       = $tariff['period_min'] + $extraDays;
        $max       = $tariff['period_max'] + $extraDays;

        return ' (' . $min . '-' . $max . ' ' . $registry->get('language')->get('cdek_shipping__days') . ')';
    }

    private static function getTotalSum(array $tariff): float
    {
        $settings = SettingsSingleton::getInstance();
        $total = $tariff['delivery_sum'];

        if ($settings->priceSettings->priceExtraPrice !== null &&
            $settings->priceSettings->priceExtraPrice >= 0) {
            $total += $settings->priceSettings->priceExtraPrice;
        }

        if ($settings->priceSettings->pricePercentageIncrease !== null &&
            $settings->priceSettings->pricePercentageIncrease > 0) {
            $added = $total / 100 * $settings->priceSettings->pricePercentageIncrease;
            $total += $added;
            $total = round($total);
        }

        if ($settings->priceSettings->priceFix !== null && $settings->priceSettings->priceFix >= 0) {
            $total = (int)$settings->priceSettings->priceFix;
        }

        if ($settings->priceSettings->priceFree !== null && $settings->priceSettings->priceFree >= 0) {
            $cartProducts = RegistrySingleton::getInstance()->get('cart')->getProducts();
            if ($cartProducts[0]['total'] > (float)$settings->priceSettings->priceFree) {
                $total = 0;
            }
        }

        return $total;
    }
}
