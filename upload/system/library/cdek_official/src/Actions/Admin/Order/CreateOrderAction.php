<?php

namespace CDEK\Actions\Admin\Order;

use Cart\Weight;
use CDEK\Config;
use CDEK\Exceptions\ValidationException;
use CDEK\Helpers\LocationHelper;
use CDEK\Helpers\LogHelper;
use CDEK\Helpers\StringHelper;
use CDEK\Models\OrderMetaRepository;
use CDEK\Models\Tariffs;
use CDEK\RegistrySingleton;
use CDEK\SettingsSingleton;
use CDEK\Transport\CdekApi;
use Exception;
use JsonException;
use Loader;
use Response;

class CreateOrderAction
{
    /**
     * @throws Exception
     */
    public function __invoke(int $orderId, int $width, int $height, int $length): void
    {
        $registry = RegistrySingleton::getInstance();

        $registry->get('load')->language('extension/shipping/cdek_official');

        /** @var Response $response */
        $response = $registry->get('response');

        try {
            self::validateCreateOrderRequest($orderId, $width, $height, $length);
        } catch (ValidationException $e) {
            $registry->get('session')->data['errors'] = [$e->getMessage()];
            $response->setOutput((new GetOrderInfoTabAction)($orderId)['content']);
            return;
        }

        $request = self::buildRequestData($orderId, $width, $height, $length);

        LogHelper::write("Sending requests to CDEK for order $orderId: " . json_encode($request, JSON_THROW_ON_ERROR));

        $result = CdekApi::createOrder($request);

        if (self::doOrderHasCreationErrors($result)) {
            $response->setOutput((new GetOrderInfoTabAction)($orderId)['content']);
            return;
        }

        OrderMetaRepository::insertCdekUuid($orderId, $result['entity']['uuid']);
        OrderMetaRepository::insertCdekTrack($orderId, '');

        LogHelper::write("Send order $orderId with uuid {$result['entity']['uuid']}");

        sleep(5); //Ожидание формирования заказа

        $response->setOutput((new GetOrderInfoTabAction)($orderId)['content']);
    }

    private static function validateCreateOrderRequest(int $orderId, int $width, int $height, int $length): void
    {
        $language = RegistrySingleton::getInstance()->get('language');

        if (!is_numeric($length) || $length < 1) {
            throw new ValidationException($language->get('cdek_error_dimensions_length_invalid'));
        }

        if ($width < 1 || !is_numeric($width)) {
            throw new ValidationException($language->get('cdek_error_dimensions_width_invalid'));
        }

        if ($height < 1 || !is_numeric($height)) {
            throw new ValidationException($language->get('cdek_error_dimensions_height_invalid'));
        }

        if (empty($orderId)) {
            throw new ValidationException($language->get('cdek_error_dimensions_order_id_empty'));
        }
    }

    private static function buildRequestData(int $orderId, int $width, int $height, int $length): array
    {
        $registry = RegistrySingleton::getInstance();
        $meta     = OrderMetaRepository::getOrder($orderId);
        $settings = SettingsSingleton::getInstance();

        /** @var Loader $loader */
        $loader = $registry->get('load');

        $loader->model('sale/order');
        $loader->model('localisation/country');

        $order = $registry->get('model_sale_order')->getOrder($orderId);

        $params = [
            'developer_key' => Config::DEVELOPER_KEY,
            'packages'      => [
                [
                    'number'  => sprintf('%s_%s', $orderId, StringHelper::generateRandom(5)),
                    'height'  => $height,
                    'length'  => $length,
                    'width'   => $width,
                    'items'   => self::getItems($order),
                    'weight'  => $meta['weight'],
                    'comment' => 'приложена опись',
                ],
            ],
            'recipient'     => [
                'name'   => $order['shipping_firstname'] . ' ' . ($order['shipping_lastname'] ?: ''),
                'phones' => [
                    [
                        'number' => $order['telephone'],
                    ],
                ],
            ],
            'sender'        => [
                'name'   => $settings->sellerSettings->shippingSellerName,
                'phones' => [
                    [
                        'number' => $settings->sellerSettings->shippingSellerPhone,
                    ],
                ],
            ],
            'number'        => $orderId,
            'tariff_code'   => explode('_', explode('.', $order['shipping_code'])[1])[1],
        ];

        if (Tariffs::isTariffToOffice($params['tariff_code'])) {
            $params['delivery_point'] = $meta['pvz_code'];
        } else {
            $params['to_location'] = [
                'city'         => $order['shipping_city'] ?? '',
                'postal_code'  => $order['shipping_postcode'] ?? '',
                'country_code' => $registry->get('model_localisation_country')
                                           ->getCountry($order['shipping_country_id'])['iso_code_2'] ?? 'RU',
                'address'      => $order['shipping_address_1'] . ' ' . ($order['shipping_address_2'] ?? ''),
            ];
        }

        if (Tariffs::isTariffFromOffice($params['tariff_code'])) {
            $office                   = LocationHelper::getLocality($settings->shippingSettings->shippingPvz);
            $params['shipment_point'] = $office['code'];
        } else {
            $address = LocationHelper::getLocality($settings->shippingSettings->shippingCityAddress);

            $params['from_location'] = [
                'postal_code'  => $address['postal'] ?? null,
                'city'         => $address['city'],
                'address'      => $address['city'],
                'country_code' => $address['country'] ?? 'RU',
            ];
        }

        if ($order['payment_code'] === 'cod') {
            $params['delivery_recipient_cost'] = [
                'value' => $order['total'],
            ];
        }

        return $params;
    }

    private static function getItems(array $order): array
    {
        $registry = RegistrySingleton::getInstance();
        $settings = SettingsSingleton::getInstance();

        /** @var Loader $loader */
        $loader = $registry->get('load');

        $loader->model('catalog/product');
        $loader->model('sale/order');

        $orderItems = $registry->get('model_sale_order')->getOrderProducts($order['order_id']);

        $weightClass = new Weight($registry);

        return array_filter(array_map(static function ($item) use ($registry, $weightClass, $settings, $order) {
            $product = $registry->get('model_catalog_product')->getProduct($item['product_id']);

            if ($product['shipping'] === '0') {
                return [];
            }

            return [
                'ware_key' => $item['order_product_id'],
                'name'     => $item['name'],
                'cost'     => $item['price'],
                'amount'   => $item['quantity'],
                'weight'   => $weightClass->convert($product['weight'],
                                                    $product['weight_class_id'],
                                                    Config::WEIGHT_CLASS_ID) ?:
                    $settings->dimensionsSettings->dimensionsWeight,
                'payment'  => [
                    'value' => $order['payment_code'] === 'cod' ? $item['price'] : 0,
                ],
            ];
        }, $orderItems));
    }

    /**
     * @throws JsonException
     */
    private static function doOrderHasCreationErrors(array $order): bool
    {
        if (empty($order['requests'][0]['errors'])) {
            return false;
        }

        $registry = RegistrySingleton::getInstance();
        LogHelper::write('Order validation errors: ' .
                         json_encode($order['requests'][0]['errors'], JSON_THROW_ON_ERROR));

        $registry->get('session')->data['errors'] = array_map(static fn($e) => $registry->get('language')
                                                                                        ->get('cdek_order_create_error_template') .
                                                                               $e['message'],
            $order['requests'][0]['errors']);
        return true;
    }
}
