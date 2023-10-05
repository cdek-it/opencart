<?php
require_once(DIR_SYSTEM . 'library/cdek_official/Settings.php');
require_once(DIR_SYSTEM . 'library/cdek_official/CdekApi.php');

class Calc
{
    private $cartProducts;
    private $settings;
    protected $registry;
    protected $cdekApi;
    protected $address;
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
                'title' => $this->registry->get('language')->get('text_title'),
                'quote' => $quoteData,
                'sort_order' => $this->registry->get('config')->get('shipping_cdek_official_sort_order'),
                'error' => false
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
        foreach ($tariffs->data as $tariff) {
            if ($tariff['enable']) {
                $data = [
                    "currency" => $currency->getSelectedCurrency(),
                    "tariff_code" => $tariff['code'],
                    "from_location" => [
                        "code" => $this->settings->shippingSettings->shippingCityCode
                    ],
                    "to_location" => [
                        "code" => $recipientLocation[0]->code
                    ],
                    "packages" => $this->getPackage()
                ];
                $result = $this->cdekApi->calculate($data);

                if (isset($result->errors)) {
                    continue;
                }

                $title = $this->registry->get('language')->get('cdek_shipping__tariff_name_' . $tariff['code']) . $this->getPeriod($result);
                $total = $this->getTotalSum($result);

                $quoteData['cdek_official_' . $tariff['code']] = [
                    'code' => 'cdek_official.cdek_official_' . $tariff['code'],
                    'title' => $title,
                    'cost' => $total,
                    'tax_class_id' => $tariff['code'],
                    'text' => $this->registry->get('currency')->format($total, $this->registry->get('session')->data['currency'])
                ];

                $tariffModel = new Tariffs();
                if ($tariffModel->getDirectionByCode($tariff['code']) === 'store' || $tariffModel->getDirectionByCode($tariff['code']) === 'postamat') {
                    $quoteData['cdek_official_' . $tariff['code']]['extra'] = $this->registry->get('load')->view('extension/shipping/cdek_official_map', [
                        'tariff' => $tariff,
                        'apikey' => $this->settings->authSettings->apiKey,
                        'city' => $recipientLocation[0]->city
                    ]);
                }
            }
        }
        return $quoteData;
    }

    private function getPeriod($calc)
    {
        $extraDays = (int)$this->settings->shippingSettings->shippingExtraDays;
        $min = $calc->period_min + $extraDays;
        $max = $calc->period_max + $extraDays;
        return ' (' . $min . '-' . $max . ')';

    }

    private function getPackage()
    {
        $packages = [];
        foreach ($this->cartProducts as $product) {
            $dimensions = $this->getDimensions($product);
            for ($i = 0; $i < (int)$product['quantity']; $i++) {
                $packages[] = $dimensions;
            }
        }

        return $packages;
    }

    private function getDimensions($product)
    {
        $dimensions = [
            "height" => (int)$product['height'],
            "length" => (int)$product['length'],
            "weight" => (int)($this->weight->convert((int)$product['weight'], $product['weight_class_id'], '2')) / (int)$product['quantity'],
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

    private function getTotalSum($result)
    {
        $total = $result->total_sum;

        if ($this->settings->priceSettings->priceExtraPrice !== '' && $this->settings->priceSettings->priceExtraPrice >= 0) {
            $total = $total + $this->settings->priceSettings->priceExtraPrice;
        }

        if ($this->settings->priceSettings->pricePercentageIncrease !== '' && $this->settings->priceSettings->pricePercentageIncrease > 0) {
            $added = $total/100*$this->settings->priceSettings->pricePercentageIncrease;
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