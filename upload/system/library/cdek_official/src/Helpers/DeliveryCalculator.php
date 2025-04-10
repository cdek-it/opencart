<?php

namespace CDEK\Helpers;

use Cart\Length;
use Cart\Weight;
use CDEK\Exceptions\HttpServerException;
use CDEK\Exceptions\UnparsableAnswerException;
use CDEK\Models\Tariffs;
use CDEK\RegistrySingleton;
use CDEK\SettingsSingleton;
use CDEK\Transport\CdekApi;
use Throwable;

class DeliveryCalculator
{
    public static function getQuoteForAddress(array $deliveryAddress): ?array
    {
        $registry = RegistrySingleton::getInstance();
        $registry->get('load')->language('extension/shipping/cdek_official');
        $cartProducts = $registry->get('cart')->getProducts();
        $weight       = $registry->get('weight');
        if (empty($cartProducts) && empty($weight)) {
            return null;
        }

        $calculatedQuote = self::calculateQuote($deliveryAddress);

        return empty($calculatedQuote) ? [] : [
            'code'       => 'cdek_official',
            'title'      => $registry->get('language')->get('text_title'),
            'quote'      => $calculatedQuote,
            'sort_order' => $registry->get('config')->get('shipping_cdek_official_sort_order'),
            'error'      => false,
        ];
    }

    private static function calculateQuote(array $deliveryAddress): array
    {
        $settings = SettingsSingleton::getInstance();
        $city     = trim($deliveryAddress['city'] ?? '');
        $postcode = trim($deliveryAddress['postcode'] ?? '');

        if (empty($city) && empty($postcode)) {
            return [];
        }

        try {
            $recipientLocation = CdekApi::getCityByParam(
                $city,
                $postcode,
            );
        } catch (UnparsableAnswerException|HttpServerException $e) {
            return [];
        }

        if (empty($recipientLocation[0]['code'])) {
            return [];
        }

        $tariffCalculated      = [];
        $recommendedDimensions = self::getPackage();

        if (empty($recommendedDimensions)) {
            return [];
        }

        $session                      = RegistrySingleton::getInstance()->get('session');
        $session->data['cdek_weight'] = $recommendedDimensions['weight'];
        $session->data['cdek_height'] = $recommendedDimensions['height'];
        $session->data['cdek_length'] = $recommendedDimensions['length'];
        $session->data['cdek_width']  = $recommendedDimensions['width'];

        if (!empty($settings->shippingSettings->shippingCityAddress)) {
            $locality = LocationHelper::getLocality($settings->shippingSettings->shippingCityAddress);
            LogHelper::write(
                'Calculator request: ' . json_encode(
                    [
                        'currency'      => $settings->shippingSettings->shippingCurrency,
                        'from_location' => [
                            'address'      => $locality['address'] ?? '',
                            'country_code' => $locality['country'] ?? '',
                            'postal_code'  => $locality['postal'] ?? '',
                            'city'         => $locality['city'] ?? '',
                        ],
                        'to_location'   => [
                            'code' => $recipientLocation[0]['code'],
                        ],
                        'packages'      => $recommendedDimensions,
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            );

            try {
                $result = CdekApi::calculate(
                    [
                        'currency' => $settings->shippingSettings->shippingCurrency,
                        'from_location' => [
                            'address' => $locality['address'] ?? '',
                            'country_code' => $locality['country'] ?? '',
                            'postal_code' => $locality['postal'] ?? '',
                            'city' => $locality['city'] ?? '',
                        ],
                        'to_location' => [
                            'code' => $recipientLocation[0]['code'],
                        ],
                        'packages' => $recommendedDimensions,
                    ],
                );
            } catch (UnparsableAnswerException|HttpServerException $e) {
                LogHelper::write('Calculator shipping city address error: ' . $e->getMessage());
                return $tariffCalculated;
            }

            LogHelper::write('Calculator response: ' . json_encode($result, JSON_THROW_ON_ERROR));

            if (!empty($result['tariff_codes'])) {
                foreach ($result['tariff_codes'] as $tariff) {
                    try {
                        if (!in_array(
                                (string)$tariff['tariff_code'],
                                $settings->shippingSettings->enabledTariffs,
                                true,
                            ) || !Tariffs::isTariffFromDoor($tariff['tariff_code'])) {
                            continue;
                        }
                    } catch (Throwable $e) {
                        continue;
                    }

                    $prefix = Tariffs::isTariffToDoor($tariff['tariff_code']) ? 'door' : 'office';

                    $tariffCalculated["{$prefix}_{$tariff['tariff_code']}"] = self::formatQuoteData($tariff);
                }
            }
        }

        if (!empty($settings->shippingSettings->shippingPvz)) {
            try {
                $locality = LocationHelper::getLocality($settings->shippingSettings->shippingPvz);
            } catch (\JsonException $e) {
            }

            LogHelper::write(
                'Calculator request: ' . json_encode(
                    [
                        'currency'      => $settings->shippingSettings->shippingCurrency,
                        'from_location' => [
                            'country_code' => $locality['country'] ?? '',
                            'postal_code'  => $locality['postal'] ?? '',
                            'city'         => $locality['city'] ?? '',
                        ],
                        'to_location'   => [
                            'code' => $recipientLocation[0]['code'],
                        ],
                        'packages'      => $recommendedDimensions,
                    ],
                    JSON_THROW_ON_ERROR,
                ),
            );

            try {
                $result = CdekApi::calculate(
                    [
                        'currency' => $settings->shippingSettings->shippingCurrency,
                        'from_location' => [
                            'country_code' => $locality['country'] ?? '',
                            'postal_code' => $locality['postal'] ?? '',
                            'city' => $locality['city'] ?? '',
                        ],
                        'to_location' => [
                            'code' => $recipientLocation[0]['code'],
                        ],
                        'packages' => $recommendedDimensions,
                    ],
                );
            } catch (UnparsableAnswerException|HttpServerException $e) {
                LogHelper::write('Calculator shipping pvz error: ' . $e->getMessage());
                return $tariffCalculated;
            }

            LogHelper::write('Calculator response: ' . json_encode($result, JSON_THROW_ON_ERROR));

            if (!empty($result['tariff_codes'])) {
                foreach ($result['tariff_codes'] as $tariff) {
                    try {
                        if (!in_array(
                                (string)$tariff['tariff_code'],
                                $settings->shippingSettings->enabledTariffs,
                                true,
                            ) || !Tariffs::isTariffFromOffice($tariff['tariff_code'])) {
                            continue;
                        }
                    } catch (Throwable $e) {
                        continue;
                    }

                    $prefix = Tariffs::isTariffToDoor($tariff['tariff_code']) ? 'door' : 'office';

                    $tariffCalculated["{$prefix}_{$tariff['tariff_code']}"] = self::formatQuoteData($tariff);
                }
            }
        }

        return $tariffCalculated;
    }

    private static function getPackage(): array
    {
        $cartProducts = RegistrySingleton::getInstance()->get('cart')->getProducts();
        $packages     = [];
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
        $settings    = SettingsSingleton::getInstance();
        $registry    = RegistrySingleton::getInstance();
        $lengthModel = new Length($registry);
        $weightModel = new Weight($registry);

        return [
            'height'   => $product['height'] ? (int)$lengthModel->convert(
                $product['height'],
                $product['length_class_id'],
                $settings->dimensionsSettings->lengthClass,
            ) : $settings->dimensionsSettings->dimensionsHeight,
            'length'   => $product['length'] ? (int)$lengthModel->convert(
                $product['length'],
                $product['length_class_id'],
                $settings->dimensionsSettings->lengthClass,
            ) : $settings->dimensionsSettings->dimensionsLength,
            'weight'   => ((int)($weightModel->convert(
                    $product['weight'],
                    $product['weight_class_id'],
                    $settings->dimensionsSettings->weightClass,
                )) / (int)$product['quantity']) ?: $settings->dimensionsSettings->dimensionsWeight,
            'width'    => $product['width'] ? (int)$lengthModel->convert(
                $product['width'],
                $product['length_class_id'],
                $settings->dimensionsSettings->lengthClass,
            ) : $settings->dimensionsSettings->dimensionsWidth,
            'quantity' => (int)$product['quantity'],
        ];
    }

    public static function getRecommendedPackage(array $packages): array
    {
        $settings        = SettingsSingleton::getInstance();
        $defaultPackages = [
            $settings->dimensionsSettings->dimensionsLength,
            $settings->dimensionsSettings->dimensionsWidth,
            $settings->dimensionsSettings->dimensionsHeight,
        ];
        $lengthList      = [];
        $widthList       = [];
        $heightList      = [];

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
            $widthList[]  = $packageProduct[1];
            $heightList[] = $packageProduct[2];
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
            'text'         => $registry->get('currency')->format(
                $total,
                $registry->get('session')->data['currency'],
            ),
        ];
    }

    private static function getPeriod(array $tariff): string
    {
        $settings  = SettingsSingleton::getInstance();
        $registry  = RegistrySingleton::getInstance();
        $extraDays = $settings->shippingSettings->shippingExtraDays;
        $min       = $tariff['period_min'] + $extraDays;
        $max       = $tariff['period_max'] + $extraDays;

        return ' (' . $min . '-' . $max . ' ' . $registry->get('language')->get('cdek_shipping__days') . ')';
    }

    private static function getTotalSum(array $tariff): float
    {
        $settings = SettingsSingleton::getInstance();

        if ($settings->priceSettings->priceFree !== null &&
            $settings->priceSettings->priceFree >= 0 &&
            RegistrySingleton::getInstance()->get('cart')->getSubTotal() > (float)$settings->priceSettings->priceFree) {
            return 0;
        }

        if ($settings->priceSettings->priceFix !== null && $settings->priceSettings->priceFix >= 0) {
            return (int)$settings->priceSettings->priceFix;
        }

        $total = $tariff['delivery_sum'];

        if ($settings->priceSettings->priceExtraPrice !== null && $settings->priceSettings->priceExtraPrice >= 0) {
            $total += $settings->priceSettings->priceExtraPrice;
        }

        if ($settings->priceSettings->pricePercentageIncrease !== null &&
            $settings->priceSettings->pricePercentageIncrease > 0) {
            $added = $total / 100 * $settings->priceSettings->pricePercentageIncrease;
            $total += $added;
            $total = round($total);
        }

        return $total;
    }
}
