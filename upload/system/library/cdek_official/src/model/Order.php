<?php

namespace CDEK\model;

use CDEK\Settings;

class Order
{
    private Settings $settings;
    private $orderOC;
    private $products;
    private $dimensions;
    private $model_catalog_product;
    private $weight;
    private $cost;
    private $pvz;
    private Tariffs $tariffs;
    private $weightPackage;


    public function __construct($settings, $orderData, $dimensions)
    {
        $this->settings = $settings;
        $this->orderOC = $orderData['orderOC'];
        $this->products = $orderData['products'];
        $this->cost = $orderData['cost'];
        $this->dimensions = $dimensions;
        $this->model_catalog_product = $orderData['modelCatalogProduct'];
        $this->weight = $orderData['weight'];
        $this->pvz = $orderData['pvz'];
        $this->tariffs = new Tariffs();
    }

    public function getRequestData()
    {
        $order = $this->getOrderData();
        $data = [
            "developer_key" => "O#UVFQ4JZa?)EV4lBC+h@dAMe^~4nKLi",
            "packages" => [
                [
                    "number" => "package_order_" . $this->orderOC['order_id'],
                    "height" => $this->dimensions['height'],
                    "length" => $this->dimensions['length'],
                    "width" => $this->dimensions['width'],
                    "items" => $this->getItems(),
                    "weight" => $this->weightPackage,

                ]
            ],
            "recipient" => [
                "name" => $order['nameCustomer'],
                "phones" => [
                    [
                        "number" => $this->orderOC['telephone']
                    ]
                ]
            ],
            "sender" => [
                "name" => $this->settings->sellerSettings->shippingSellerName,
                "phones" => [
                    [
                        "number" => $this->settings->sellerSettings->shippingSellerPhone
                    ]
                ]
            ],
            "tariff_code" => $order['tariffCodeCustomer']
        ];

        $deliveryRecipientCost = [];
        if ($this->orderOC['payment_code'] === 'cod') {
            $deliveryRecipientCost = [
                "delivery_recipient_cost" => [
                    "value" => $this->cost
                ]
            ];
        }

        $tariffCode = (int)$order['tariffCodeCustomer'];

        return array_merge(
            $data,
            $this->getFromByTariffCode($tariffCode),
            $this->getToByTariffCode($tariffCode, $order),
            $deliveryRecipientCost
        );
    }

    private function getOrderData(): array
    {
        $tariffNameParts = explode('_', $this->orderOC['shipping_code']);
        $tariffCodeCustomer = end($tariffNameParts);
        return [
            'nameCustomer' => $this->orderOC['firstname'] . ' ' . $this->orderOC['lastname'],
            'addressCustomer' => $this->orderOC['shipping_address_1'] . ' ' . $this->orderOC['shipping_address_2'],
            'postcodeCustomer' => $this->orderOC['shipping_postcode'],
            'tariffCodeCustomer' => $tariffCodeCustomer,
            'paymentCodeCustomer' => $this->orderOC['payment_code']
        ];
    }

    private function getItems()
    {
        $data = [];
        foreach ($this->products as $product) {
            $productOC = $this->model_catalog_product->getProduct($product['product_id']);
            //            weight_class_id

            if ((int)$productOC['tax_class_id'] === 10) {
                continue;
            }

            $weight = (int)$this->weight->convert((int)$productOC['weight'], $productOC['weight_class_id'], '2');
            if ($weight === 0) {
                $weight = $this->settings->dimensionsSettings->dimensionsWeight;
            }
            $tmp = [
                "ware_key" => "product_id_" . $product['product_id'],
                "name" => $product['name'],
                "cost" => $product['price'],
                "amount" => $product['quantity'],
                "weight" => $weight,
            ];

            if ($this->orderOC['payment_code'] === 'cod') {
                $tmp["payment"] = [
                    "value" => $product['price']
                ];
            } else {
                $tmp["payment"] = [
                    "value" => 0
                ];
            }
            $data[] = $tmp;

            $this->weightPackage += $weight;
        }
        return $data;
    }

    private function getFromByTariffCode(int $tariffCode)
    {
        if ($this->tariffs->getFromByCode($tariffCode) === "door") {
            $result = [
                "from_location" => [
                    "address" => $this->settings->shippingSettings->shippingCityAddress
                ]
            ];
        } else {
            $result = [
                "shipment_point" => trim(explode(',', $this->settings->shippingSettings->shippingPvz)[1])
            ];
        }
        return $result;
    }

    private function getToByTariffCode(int $tariffCode, $order)
    {
        if ($this->tariffs->getDirectionByCode($tariffCode) === "door") {
            $result = [
                "to_location" => [
                    "postal_code" => $order['postcodeCustomer'],
                    "address" => $order['addressCustomer']
                ],
            ];
        } else {
            $result = [
                "delivery_point" => $this->pvz
            ];
        }
        return $result;
    }
}