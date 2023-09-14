<?php
require_once(DIR_SYSTEM . 'library/cdek_official/Settings.php');
require_once(DIR_SYSTEM . 'library/cdek_official/CdekApi.php');

class Calc
{
    private $cartProducts;
    private $settings;
    protected $registry;
    protected $cdekApi;
    protected $postcode;
    private $weight;

    public function __construct($registry, $cartProducts, $modelSettings, $postcode, $weight)
    {
        $this->cartProducts = $cartProducts;
        $this->registry = $registry;
        $this->registry->get('load')->language('extension/shipping/cdek_official');
        $this->settings = new Settings();
        $this->settings->init($modelSettings);
        $this->cdekApi = new CdekApi($registry, $this->settings);
        $this->postcode = $postcode;
        $this->weight = $weight;
    }

    public function getMethodData()
    {
        $quoteData = $this->getQuote();

        $methodData = array(
            'code' => 'cdek_official',
            'title' => $this->registry->get('language')->get('text_title'),
            'quote' => $quoteData,
            'sort_order' => $this->registry->get('config')->get('shipping_cdek_official_sort_order'),
            'error' => false
        );

        return $methodData;
    }

    private function getQuote()
    {
        $tariffs = $this->settings->shippingSettings->tariffs;
        $currency = $this->settings->shippingSettings->currency;
        $quoteData = [];
        $this->registry->get('currency');
        $recipientLocation = $this->cdekApi->getCityByPostcode($this->postcode);
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

                $quoteData['cdek_official_' . $tariff['code']] = [
                    'code' => 'cdek_official.cdek_official_' . $tariff['code'],
                    'title' => $this->registry->get('language')->get('cdek_shipping__tariff_name_' . $tariff['code']),
                    'cost' => $result->total_sum,
                    'tax_class_id' => $tariff['code'],
                    'text' => $this->registry->get('currency')->format($result->total_sum, $this->registry->get('session')->data['currency'])
                ];
            }
        }
        return $quoteData;
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
        if ($this->settings->dimensionsSettings->dimensionsUseDefault === 'on') {
            $dimensions = [
                "height" => (int)$this->settings->dimensionsSettings->dimensionsHeight,
                "length" => (int)$this->settings->dimensionsSettings->dimensionsLength,
                "width" => (int)$this->settings->dimensionsSettings->dimensionsWidth,
                "weight" => (int)$this->settings->dimensionsSettings->dimensionsWeight
            ];
        } else {
            $dimensions = [
                "height" => (int)$product['height'],
                "length" => (int)$product['length'],
                "weight" => ($this->weight->convert((int)$product['weight'], $product['weight_class_id'], '2'))/(int)$product['quantity'],
                "width" => (int)$product['width']
            ];
        }
        return $dimensions;
    }
}