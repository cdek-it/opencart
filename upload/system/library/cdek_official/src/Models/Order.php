<?php

namespace CDEK\Models;

use CDEK\Config;
use CDEK\CdekHelper;
use CDEK\Helpers\DeliveryCalculator;
use CDEK\SettingsSingleton;

class Order
{
    private SettingsSingleton $settings;
    private $orderOC;
    private $products;
    private $model_catalog_product;
    private $weight;
    private $cost;
    private $pvz;
    private int $weightPackage = 0;


    public function __construct(SettingsSingleton $settings, array $orderData)
    {
        $this->settings              = $settings;
        $this->orderOC               = $orderData['orderOC'];
        $this->products              = $orderData['products'];
        $this->cost                  = $orderData['cost'];
        $this->model_catalog_product = $orderData['modelCatalogProduct'];
        $this->weight                = $orderData['weight'];
        $this->pvz                   = $orderData['pvz'];
    }

    final public function getRequestData(): array
    {
        $order        = $this->getOrderData();
        $packageOrder = $this->getOrderPackage();
        $data         = [
            'developer_key' => Config::DEVELOPER_KEY,
            'packages'      => [
                [
                    'number' => "package_order_{$this->orderOC['order_id']}",
                    'height' => $packageOrder['height'],
                    'length' => $packageOrder['length'],
                    'width'  => $packageOrder['width'],
                    'items'  => $this->getItems(),
                    'weight' => $packageOrder['weight'],
                ],
            ],
            'recipient'     => [
                'name'   => $order['nameCustomer'],
                'phones' => [
                    [
                        'number' => $this->orderOC['telephone'],
                    ],
                ],
            ],
            'sender'        => [
                'name'   => $this->settings->sellerSettings->shippingSellerName,
                'phones' => [
                    [
                        'number' => $this->settings->sellerSettings->shippingSellerPhone,
                    ],
                ],
            ],
            'tariff_code'   => $order['tariffCodeCustomer'],
        ];

        if ($this->orderOC['payment_code'] === 'cod') {
            $data['delivery_recipient_cost'] = [
                'value' => $this->cost,
            ];
        }

        $tariffCode = (int)$order['tariffCodeCustomer'];

        return array_merge($data,
                           $this->getDepartureByTariffCode($tariffCode),
                           $this->getDestinationByTariffCode($tariffCode, $order));
    }

    private function getOrderData(): array
    {
        $tariffNameParts = explode('_', $this->orderOC['shipping_code']);
        return [
            'nameCustomer'        => $this->orderOC['firstname'] . ' ' . $this->orderOC['lastname'],
            'addressCustomer'     => $this->orderOC['shipping_address_1'] . ' ' . $this->orderOC['shipping_address_2'],
            'postcodeCustomer'    => $this->orderOC['shipping_postcode'],
            'tariffCodeCustomer'  => end($tariffNameParts),
            'paymentCodeCustomer' => $this->orderOC['payment_code'],
        ];
    }

    private function getOrderPackage(): array
    {
        $productPackages = [];
        foreach ($this->products as $key => $product) {
            $productOC         = $this->model_catalog_product->getProduct($product['product_id']);
            $productPackages[] = [
                'length'   => (int)$productOC['length'],
                'width'    => (int)$productOC['width'],
                'height'   => (int)$productOC['height'],
                'weight'   => (int)($this->weight->convert($productOC['weight'], $productOC['weight_class_id'], '2')),
                'quantity' => (int)$this->products[$key]['quantity'],
            ];
        }
        return DeliveryCalculator::getRecommendedPackage($productPackages);
    }

    private function getItems(): array
    {
        $data = [];

        foreach ($this->products as $product) {
            $productOC = $this->model_catalog_product->getProduct($product['product_id']);

            if ((int)$productOC['tax_class_id'] === 10) {
                continue;
            }

            $weight = (int)$this->weight->convert($productOC['weight'], $productOC['weight_class_id'], '2');
            if ($weight === 0) {
                $weight = $this->settings->dimensionsSettings->dimensionsWeight;
            }

            $data[] = [
                'ware_key' => "product_id_{$product['product_id']}",
                'name'     => $product['name'],
                'cost'     => $product['price'],
                'amount'   => $product['quantity'],
                'weight'   => $weight,
                'payment'  => [
                    'value' => $this->orderOC['payment_code'] === 'cod' ? $product['price'] : 0,
                ],
            ];

            $this->weightPackage += $weight;
        }
        return $data;
    }

    private function getDepartureByTariffCode(int $tariffCode): array
    {
        $result = [];
        if (Tariffs::isTariffFromDoor($tariffCode)) {
            $locality = CdekHelper::getLocality($this->settings->shippingSettings->shippingCityAddress);
            if (CdekHelper::checkLocalityAddress($locality)) {
                $result = [
                    'from_location' => [
                        "address"      => $locality->address ?? '',
                        'country_code' => $locality->country ?? '',
                        'postal_code'  => $locality->postal ?? '',
                        'city'         => $locality->city ?? '',
                    ],
                ];
            }
        } else {
            $locality = CdekHelper::getLocality($this->settings->shippingSettings->shippingPvz);
            if (CdekHelper::hasLocalityCode($locality)) {
                $result = [
                    'shipment_point' => $locality->code ?? '',
                ];
            }
        }
        return $result;
    }

    private function getDestinationByTariffCode(int $tariffCode, array $order): array
    {
        return Tariffs::isTariffToDoor($tariffCode) ? [
            'to_location' => [
                'postal_code' => $order['postcodeCustomer'],
                'address'     => $order['addressCustomer'],
            ],
        ] : [
            'delivery_point' => $this->pvz,
        ];
    }
}
