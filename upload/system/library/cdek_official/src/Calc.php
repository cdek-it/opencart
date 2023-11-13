<?php

namespace CDEK;

use CDEK\model\Tariffs;

class Calc
{
    protected $registry;
    protected $cdekApi;
    protected $address;
    private $cartProducts;
    private $settings;
    private $weight;
    private $link;

    public function __construct($registry, $cartProducts, $modelSettings, $address, $weight)
    {
        $this->cartProducts = $cartProducts;
        $this->registry = $registry;
        $this->registry->get('load')->language('extension/shipping/cdek_official');
        $this->settings = new Settings();
        $this->settings->init($modelSettings);
        $this->cdekApi = new CdekApi($registry, $this->settings);
        $this->address = $address;
        $this->weight = $weight;
        $this->link = $this->registry->get('link');
    }

    public function getMethodData()
    {
        $quoteData = $this->getQuote();
        $methodData = [];

        if (!empty($quoteData)) {
            $methodData = array(
                'code' => 'cdek_official',
                'title' => $this->registry->get('language')->get('text_title') . $this->registry->get('load')->view('extension/shipping/cdek_official_checkout_inputs'),
                'quote' => $quoteData,
                'sort_order' => $this->registry->get('config')->get('shipping_cdek_official_sort_order'),
                'error' => false,
            );
        }

        return $methodData;
    }

    private function getQuote()
    {
        $tariffs = $this->settings->shippingSettings->tariffs;
        $currency = $this->settings->shippingSettings->currency;
        $quoteData = [];
        $this->registry->get('currency');
        $recipientLocation = $this->cdekApi->getCityByParam($this->address['city'], $this->address['postcode']);
        if (empty($recipientLocation)) {
            return [];
        }
//        if (empty($recipientLocation)) {
//            $tariffPlugName = $this->getTariffPlugName();
//            $quoteData['cdek_official_tariff_plug'] = [
//                'code' => 'cdek_official.cdek_official_tariff_plug',
//                'title' => $tariffPlugName,
//                'cost' => 0,
//                'tax_class_id' => 0,
//                'text' => ('address incorrect')
//            ];
//            return $quoteData;
//        }

        //От двери
        $tariffCalculatedToDoor = [];
        $package = $this->getPackage();

        if (empty($package)) {
            return [];
        }

        $currencySelected = $currency->getSelectedCurrency();
        $toLocationCode = $recipientLocation[0]->code;
        if (!empty($this->settings->shippingSettings->shippingCityAddress)) {
            //[0] - country_code, [1] - postal_code, [2] - city,
            $senderLocality = explode(':', $this->settings->shippingSettings->shippingSenderLocality);
            $data = [
                "currency" => $currencySelected,
                "from_location" => [
                    "address" => $this->settings->shippingSettings->shippingCityAddress,
                    'country_code' => $senderLocality[0],
                    'postal_code' => $senderLocality[1],
                    'city' => $senderLocality[2],
                ],
                "to_location" => [
                    "code" => $toLocationCode
                ],
                "packages" => $package
            ];
            $result = $this->cdekApi->calculate($data);
            foreach ($result->tariff_codes as $tariff) {
                if (in_array($tariff->delivery_mode, [1, 2, 6])) {
                    $tariffCalculatedToDoor[] = $tariff;
                }
            }
        }

        //От пвз
        $tariffCalculatedToPvz = [];
        if (!empty($this->settings->shippingSettings->shippingPvz)) {
            $data = [
                "currency" => $currencySelected,
                "from_location" => [
                    "address" => explode(',', $this->settings->shippingSettings->shippingPvz)[0]
                ],
                "to_location" => [
                    "code" => $toLocationCode
                ],
                "packages" => $package
            ];
            $result = $this->cdekApi->calculate($data);
            foreach ($result->tariff_codes as $tariff) {
                if (!in_array($tariff->delivery_mode, [1, 2, 6])) {
                    $tariffCalculatedToPvz[] = $tariff;
                }
            }
        }

        $tariffCalculated = array_merge($tariffCalculatedToDoor, $tariffCalculatedToPvz);

        $tariffCodeEnable = [];
        foreach ($tariffs->data as $tariff) {
            if ($tariff['enable']) {
                $tariffCodeEnable[] = $tariff['code'];
            }
        }

        foreach ($tariffCalculated as $tariff) {
            if (in_array($tariff->tariff_code, $tariffCodeEnable)) {
                $title = $this->registry->get('language')->get('cdek_shipping__tariff_name_' . $tariff->tariff_code) . $this->getPeriod($tariff);
                $total = $this->getTotalSum($tariff);

                $quoteData['cdek_official_' . $tariff->tariff_code] = [
                    'code' => 'cdek_official.cdek_official_' . $tariff->tariff_code,
                    'title' => 'CDEK: ' . $title,
                    'cost' => $total,
                    'tax_class_id' => $tariff->tariff_code,
                    'text' => $this->registry->get('currency')->format($total,
                        $this->registry->get('session')->data['currency'])
                ];

                $tariffModel = new Tariffs();
                if ($tariffModel->getDirectionByCode($tariff->tariff_code) === 'store' || $tariffModel->getDirectionByCode($tariff->tariff_code) === 'postamat') {
                    $offices = $this->cdekApi->getOffices($recipientLocation[0]->code);

                    $quoteData['cdek_official_' . $tariff->tariff_code]['title'] .= $this->registry->get('load')->view('extension/shipping/cdek_official_map',
                        [
                            'tariff' => $tariff->tariff_code,
                            'apikey' => $this->settings->authSettings->apiKey,
                            'city' => $recipientLocation[0]->city,
                            'offices' => json_encode($offices)
                        ]);
                }
            }
        }
        return $quoteData;
    }

    private function getPackage()
    {
        $packages = [];
        foreach ($this->cartProducts as $product) {
            if ((int)$product['tax_class_id'] !== 10) {
                $dimensions = $this->getDimensions($product);
                for ($i = 0; $i < (int)$product['quantity']; $i++) {
                    $packages[] = $dimensions;
                }
            }
        }

        return $packages;
    }

    private function getDimensions($product)
    {
        $dimensions = [
            "height" => (int)$product['height'],
            "length" => (int)$product['length'],
            "weight" => (int)($this->weight->convert((int)$product['weight'], $product['weight_class_id'],
                    '2')) / (int)$product['quantity'],
            "width" => (int)$product['width']
        ];

        if ($dimensions["height"] === 0) {
            $dimensions["height"] = (int)$this->settings->dimensionsSettings->dimensionsHeight;
        }

        if ($dimensions["width"] === 0) {
            $dimensions["width"] = (int)$this->settings->dimensionsSettings->dimensionsWidth;
        }

        if ($dimensions["length"] === 0) {
            $dimensions["length"] = (int)$this->settings->dimensionsSettings->dimensionsLength;
        }

        if ($dimensions["weight"] === 0) {
            $dimensions["weight"] = (int)$this->settings->dimensionsSettings->dimensionsWeight;
        }

        return $dimensions;
    }

    private function getPeriod($calc)
    {
        $extraDays = (int)$this->settings->shippingSettings->shippingExtraDays;
        $min = $calc->period_min + $extraDays;
        $max = $calc->period_max + $extraDays;
        return ' (' . $min . '-' . $max . ' ' . $this->registry->get('language')->get('cdek_shipping__days') . ')';

    }

    private function getTotalSum($result)
    {
        $total = $result->delivery_sum;

        if ($this->settings->priceSettings->priceExtraPrice !== '' && $this->settings->priceSettings->priceExtraPrice >= 0) {
            $total = $total + $this->settings->priceSettings->priceExtraPrice;
        }

        if ($this->settings->priceSettings->pricePercentageIncrease !== '' && $this->settings->priceSettings->pricePercentageIncrease > 0) {
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

    /**
     * @return mixed
     */
    protected function getTariffPlugName()
    {
        if (empty($this->settings->shippingSettings->shippingTariffPlug)) {
            $tariffPlugName = $this->registry->get('language')->get('cdek_shipping__tariff_name_plug');
        } else {
            $tariffPlugName = $this->settings->shippingSettings->shippingTariffPlug;
        }
        return $tariffPlugName;
    }
}
