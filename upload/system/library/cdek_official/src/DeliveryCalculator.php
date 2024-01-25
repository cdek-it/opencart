<?php

namespace CDEK;

use CDEK\Models\Tariffs;

class DeliveryCalculator
{
    protected $registry;
    protected $cdekApi;
    protected $address;
    private $cartProducts;
    private $settings;
    private $weight;
    private $link;

    public function __construct($cartProducts, $address, $weight)
    {
        $this->cartProducts = $cartProducts;
        $registry           = RegistrySingleton::getInstance();
        $registry->get('load')->language('extension/shipping/cdek_official');
        $this->settings = new Settings;
        $this->settings->init($modelSettings);
        $this->cdekApi = new CdekApi($this->settings);
        $this->address = $address;
        $this->weight  = $weight;
        $this->link    = $registry->get('link');
    }


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
            'quote'      => $quoteData,
            'sort_order' => $registry->get('config')->get('shipping_cdek_official_sort_order'),
            'error'      => false,
        ];
    }

    private function _getQuote()
    {
        $currency  = $this->settings->shippingSettings->currency;
        $quoteData = [];
        $this->registry->get('currency');
        $recipientLocation = $this->cdekApi->getCityByParam(trim($this->address['city'] ?? ''),
                                                            trim($this->address['postcode'] ?? ''));
        if (empty($recipientLocation)) {
            return [];
        }

        $tariffCalculated      = [];
        $recommendedDimensions = $this->getRecommendedPackage($this->getPackage());

        if (empty($recommendedDimensions)) {
            return [];
        }

        $currencySelected = $currency->getSelectedCurrency();
        $toLocationCode   = $recipientLocation[0]->code;
        if (!empty($this->settings->shippingSettings->shippingCityAddress)) {
            $locality = CdekHelper::getLocality($this->settings->shippingSettings->shippingCityAddress);
            $data     = [
                'currency'      => $currencySelected,
                'from_location' => [
                    'address'      => $locality->address ?? '',
                    'country_code' => $locality->country ?? '',
                    'postal_code'  => $locality->postal ?? '',
                    'city'         => $locality->city ?? '',
                ],
                'to_location'   => [
                    'code' => $toLocationCode,
                ],
                'packages'      => $recommendedDimensions,
            ];
            $result   = $this->cdekApi->calculate($data);
            if (!empty($result) && isset($result->tariff_codes)) {
                foreach ($result->tariff_codes as $tariff) {
                    if (!in_array($tariff->tariff_code, $this->settings->shippingSettings->enabledTariffs, true) ||
                        Tariffs::isTariffFromDoor($tariff->tariff_code)) {
                        continue;
                    }
                    $tariffCalculated['cdek_official_' . $tariff->tariff_code] = $this->formatQuoteData($tariff);
                }
            }
        }

        if (!empty($this->settings->shippingSettings->shippingPvz)) {
            $locality = CdekHelper::getLocality($this->settings->shippingSettings->shippingPvz);
            if (CdekHelper::checkLocalityOffice($locality)) {
                $data   = [
                    'currency'      => $currencySelected,
                    'from_location' => [
                        'country_code' => $locality->country ?? '',
                        'postal_code'  => $locality->postal ?? '',
                        'city'         => $locality->city ?? '',
                    ],
                    'to_location'   => [
                        'code' => $toLocationCode,
                    ],
                    'packages'      => $recommendedDimensions,
                ];
                $result = $this->cdekApi->calculate($data);
                foreach ($result->tariff_codes as $tariff) {
                    if (!in_array($tariff->tariff_code, $this->settings->shippingSettings->enabledTariffs, true) ||
                        Tariffs::isTariffFromOffice($tariff->tariff_code)) {
                        continue;
                    }

                    $tariffCalculated['cdek_official_' . $tariff->tariff_code] = $this->formatQuoteData($tariff);
                }
            }
        }

        return $tariffCalculated;
    }

    private function getRecommendedPackage(array $packages)
    {
        return CdekHelper::calculateRecomendedPackage($packages, [
            'length' => (int)$this->settings->dimensionsSettings->dimensionsLength,
            'width'  => (int)$this->settings->dimensionsSettings->dimensionsWidth,
            'height' => (int)$this->settings->dimensionsSettings->dimensionsHeight,
            'weight' => (int)$this->settings->dimensionsSettings->dimensionsWeight,
        ]);
    }

    private function getPackage()
    {
        $packages = [];
        foreach ($this->cartProducts as $product) {
            if ((int)$product['tax_class_id'] !== 10) {
                $dimensions = $this->getDimensions($product);
                $packages[] = $dimensions;
            }
        }

        return $packages;
    }

    private function getDimensions(array $product): array
    {
        return [
            'height'   => ((int)$product['height']) ?: (int)$this->settings->dimensionsSettings->dimensionsHeight,
            'length'   => ((int)$product['length']) ?: (int)$this->settings->dimensionsSettings->dimensionsLength,
            'weight'   => ((int)($this->weight->convert($product['weight'], $product['weight_class_id'], '2')) /
                           (int)$product['quantity']) ?: (int)$this->settings->dimensionsSettings->dimensionsWeight,
            'width'    => ((int)$product['width']) ?: (int)$this->settings->dimensionsSettings->dimensionsWidth,
            'quantity' => (int)$product['quantity'],
        ];
    }

    private function formatQuoteData(object $tariff): array
    {
        $registry = RegistrySingleton::getInstance();

        $title = $registry->get('language')->get('cdek_shipping__tariff_name_' . $tariff->tariff_code) .
                 $this->getPeriod($tariff);
        $total = $this->getTotalSum($tariff);

        return [
            'code'         => 'cdek_official.' .
                              (Tariffs::isTariffToDoor($tariff->tariff_code) ? 'door_' : 'office_') .
                              $tariff->tariff_code,
            'title'        => $registry->get('language')->get('text_title') . ': ' . $title,
            'cost'         => $total,
            'tax_class_id' => $tariff->tariff_code,
            'text'         => $registry->get('currency')->format($total,
                                                                 $registry->get('session')->data['currency']),
        ];
    }

    private function getPeriod($calc)
    {
        $extraDays = (int)$this->settings->shippingSettings->shippingExtraDays;
        $min       = $calc->period_min + $extraDays;
        $max       = $calc->period_max + $extraDays;

        return ' (' . $min . '-' . $max . ' ' . $this->registry->get('language')->get('cdek_shipping__days') . ')';
    }

    private function getTotalSum($result)
    {
        $total = $result->delivery_sum;

        if ($this->settings->priceSettings->priceExtraPrice !== '' &&
            $this->settings->priceSettings->priceExtraPrice >= 0) {
            $total = $total + $this->settings->priceSettings->priceExtraPrice;
        }

        if ($this->settings->priceSettings->pricePercentageIncrease !== '' &&
            $this->settings->priceSettings->pricePercentageIncrease > 0) {
            $added = $total / 100 * $this->settings->priceSettings->pricePercentageIncrease;
            $total = $total + $added;
            $total = round($total);
        }

        if ($this->settings->priceSettings->priceFix !== '' && $this->settings->priceSettings->priceFix >= 0) {
            $total = (int)$this->settings->priceSettings->priceFix;
        }

        if ($this->settings->priceSettings->priceFree !== '' && $this->settings->priceSettings->priceFree >= 0) {
            if ($this->cartProducts[0]['total'] > (float)$this->settings->priceSettings->priceFree) {
                $total = 0;
            }
        }

        return $total;
    }
}
