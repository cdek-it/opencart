<?php

class Order
{
    public string $number;
    public int $tariffCode;
    public string $developerKey;
    public string $shipmentPoint;
    public string $deliveryPoint;
    public string $dateInvoice;
    public string $shipperName;
    public string $shipperAddress;
    public DeliveryRecipientCost $deliveryRecipientCost;
    public DeliveryRecipientCostAdv $deliveryRecipientCostAdv;
    public array $sender;
    public array $seller;
    public array $recipient;
    public array $fromLocation;
    public array $toLocation;
    public array $service;
    public array $packages;
    public string $print;
    public bool $isClientReturn;
    private Settings $settings;
    private $orderOC;


    public function __construct($settings, $orderOC)
    {

        $this->settings = $settings;
        $this->orderOC = $orderOC;
    }

    public function getRequestData()
    {
        $order = $this->getOrderData();
        return [
            "from_location" => [
                "code" => $this->settings->shippingSettings->shippingCityCode,
                "address" => $this->settings->shippingSettings->shippingCityAddress
            ],
            "to_location" => [
                "postal_code" => $order['postcodeCustomer'],
                "address" => $order['addressCustomer']
            ],
            "packages" => [
                [
                    "number" => "order_id",
                    "height" => 10,
                    "length" => 10,
                    "width" => 10,
                    "weight" => 4000,
                    "items" => [
                        [
                            "ware_key" => "00055",
                            "payment" => [
                                "value" => 3000
                            ],
                            "name" => "Товар",
                            "cost" => 300,
                            "amount" => 1,
                            "weight" => 700,
                        ]
                    ],

                ]
            ],
            "recipient" => [
                "name" => $order['nameCustomer'],
                "phones" => [
                    [
                        "number" => "+79134637228"
                    ]
                ]
            ],
            "tariff_code" => $order['tariffCodeCustomer']
        ];
    }

    private function getOrderData(): array
    {
        $tariffNameParts = explode('_', $this->orderOC['shipping_code']);
        $tariffCodeCustomer = end($tariffNameParts);
        return [
            'nameCustomer' => $this->orderOC['shipping_firstname'] . ' ' . $this->orderOC['shipping_lastname'],
            'addressCustomer' => $this->orderOC['shipping_address_1'] . ' ' . $this->orderOC['shipping_address_2'],
            'postcodeCustomer' => $this->orderOC['shipping_postcode'],
            'tariffCodeCustomer' => $tariffCodeCustomer,
            'paymentCodeCustomer' => $this->orderOC['payment_code']
        ];
    }
}

//$phpArray = [
//    "number" => "ddOererre7450813980068",
//    "comment" => "Новый заказ",
//    "delivery_recipient_cost" => [
//        "value" => 50
//    ],
//    "delivery_recipient_cost_adv" => [
//        [
//            "sum" => 3000,
//            "threshold" => 200
//        ]
//    ],
//    "from_location" => [
//        "code" => "44",
//        "fias_guid" => "",
//        "postal_code" => "",
//        "longitude" => "",
//        "latitude" => "",
//        "country_code" => "",
//        "region" => "",
//        "sub_region" => "",
//        "city" => "Москва",
//        "kladr_code" => "",
//        "address" => "пр. Ленинградский, д.4"
//    ],
//    "to_location" => [
//        "code" => "270",
//        "fias_guid" => "",
//        "postal_code" => "",
//        "longitude" => "",
//        "latitude" => "",
//        "country_code" => "",
//        "region" => "",
//        "sub_region" => "",
//        "city" => "Новосибирск",
//        "kladr_code" => "",
//        "address" => "ул. Блюхера, 32"
//    ],
//    "packages" => [
//        [
//            "number" => "bar-001",
//            "comment" => "Упаковка",
//            "height" => 10,
//            "items" => [
//                [
//                    "ware_key" => "00055",
//                    "payment" => [
//                        "value" => 3000
//                    ],
//                    "name" => "Товар",
//                    "cost" => 300,
//                    "amount" => 2,
//                    "weight" => 700,
//                    "url" => "www.item.ru"
//                ]
//            ],
//            "length" => 10,
//            "weight" => 4000,
//            "width" => 10
//        ]
//    ],
//    "recipient" => [
//        "name" => "Иванов Иван",
//        "phones" => [
//            [
//                "number" => "+79134637228"
//            ]
//        ]
//    ],
//    "sender" => [
//        "name" => "Петров Петр"
//    ],
//    "services" => [
//        [
//            "code" => "SECURE_PACKAGE_A2"
//        ]
//    ],
//    "tariff_code" => 139
//];