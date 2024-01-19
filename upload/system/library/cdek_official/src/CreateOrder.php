<?php

namespace CDEK;

use CDEK\model\Order;
use Registry;

class CreateOrder
{
    private $registry;
    private $settings;
    private $cdekApi;

    public function __construct(Registry $registry, $settings, CdekApi $cdekApi)
    {
        $this->registry = $registry;
        $this->settings = $settings;
        $this->cdekApi  = $cdekApi;
    }

    public function create()
    {
        $dimensions = $this->registry->get('request')->post['dimensions'];
        $orderId    = (int)$this->registry->get('request')->post['order_id'];
        $this->validateCreateOrderRequest($dimensions, $orderId);
        $orderData = $this->getData($orderId);
        $order     = new Order($this->settings, $orderData, $dimensions);
        $response  = $this->cdekApi->createOrder($order);
        CdekLog::sendLog("Order created: " . json_encode($response));
        if (CdekApiValidate::createApiValidate($response)) {
            sleep(5); //Ожидание формирования заказа
            $order = $this->cdekApi->getOrderByUuid($response->entity->uuid);
            if ($order->requests[0]->state === 'INVALID') {
                echo json_encode([
                                     'state'   => false,
                                     'message' => $this->registry->get('language')
                                                                 ->get('cdek_order_create_error_template') .
                                                  $order->requests[0]->errors[0]->message,
                                 ]);
            } else {
                $data = [
                    'cdek_number'  => $order->entity->cdek_number
                                      ??
                                      $this->registry->get('language')->get('cdek_error_cdek_number_empty'),
                    'cdek_uuid'    => $order->entity->uuid,
                    'name'         => $order->entity->recipient->name,
                    'type'         => isset($order->entity->delivery_mode) ?
                        $this->getDeliveryModeName((int)$order->entity->delivery_mode) : null,
                    'payment_type' => $this->getPaymentTypeName($orderData['orderOC']['payment_code']),
                    'to_location'  => $order->entity->to_location->city
                                      ??
                                      '' . ', ' . $order->entity->to_location->address,
                    'pvz_code'     => $order->entity->delivery_point ?? '',
                ];
                CdekOrderMetaRepository::insertOrderMeta($this->registry->get('db'), $data, $orderId);
                CdekLog::sendLog("Order validated");
                echo json_encode(['state' => true, 'data' => $data]);
            }
        } else {
            CdekLog::sendLog("Order not validated");
            $message = $response->requests[0]->errors[0]->message;

            if (strpos($response->requests[0]->errors[0]->message, 'length')) {
                $message = $this->registry->get('language')->get('cdek_error_dimensions_length_package_invalid');
            }
            if (strpos($response->requests[0]->errors[0]->message, 'width')) {
                $message = $this->registry->get('language')->get('cdek_error_dimensions_width_package_invalid');
            }
            if (strpos($response->requests[0]->errors[0]->message, 'height')) {
                $message = $this->registry->get('language')->get('cdek_error_dimensions_height_package_invalid');
            }

            echo json_encode(['state' => false, 'message' => $message]);
        }
        exit;
    }

    private function validateCreateOrderRequest($dimensions, $orderId)
    {
        $validate = ['state' => true];

        $length = intval($dimensions['length']);
        $width  = intval($dimensions['width']);
        $height = intval($dimensions['height']);

        if ($length < 0 || !is_numeric($dimensions['length']) || $dimensions['length'] === '0') {
            $validate = [
                'state'   => false,
                'message' => $this->registry->get('language')->get('cdek_error_dimensions_length_invalid'),
            ];
        } elseif ($width < 0 || !is_numeric($dimensions['width']) || $dimensions['width'] === '0') {
            $validate = [
                'state'   => false,
                'message' => $this->registry->get('language')->get('cdek_error_dimensions_width_invalid'),
            ];
        } elseif ($height < 0 || !is_numeric($dimensions['height']) || $dimensions['height'] === '0') {
            $validate = [
                'state'   => false,
                'message' => $this->registry->get('language')->get('cdek_error_dimensions_height_invalid'),
            ];
        } elseif (empty($orderId)) {
            $validate = [
                'state'   => false,
                'message' => $this->registry->get('language')->get('cdek_error_dimensions_order_id_empty'),
            ];
        }

        if (!$validate['state']) {
            echo json_encode($validate);
            exit;
        }
    }

    protected function getData($orderId): array
    {
        $query = CdekOrderMetaRepository::getOrder($this->registry->get('db'), $orderId);
        $pvz   = $this->getPvz($query);
        $this->registry->get('load')->model('sale/order');
        $this->registry->get('load')->model('catalog/product');
        $modelSaleOrder      = $this->registry->get('model_sale_order');
        $modelCatalogProduct = $this->registry->get('model_catalog_product');
        $orderTotals         = $modelSaleOrder->getOrderTotals($orderId);
        $cost                = $orderTotals[1]['value'];
        $weight              = $this->registry->get('weight');
        $orderOC             = $modelSaleOrder->getOrder($orderId);
        $products            = $modelSaleOrder->getOrderProducts($orderId);

        return [
            'pvz'                 => $pvz,
            'modelCatalogProduct' => $modelCatalogProduct,
            'weight'              => $weight,
            'orderId'             => $orderId,
            'orderOC'             => $orderOC,
            'products'            => $products,
            'cost'                => $cost,
        ];
    }

    protected function getPvz($query)
    {
        return !empty($query->row['pvz_code']) ? $query->row['pvz_code'] : '';
    }

    private function getDeliveryModeName(int $deliveryMode)
    {
        if (in_array($deliveryMode, [1, 3, 8])) {
            return $this->registry->get('language')->get('cdek_shipping__tariff_type_to_door');
        }

        return $this->registry->get('language')->get('cdek_shipping__tariff_type_to_warehouse');
    }

    private function getPaymentTypeName($paymentCode)
    {
        if ($paymentCode === 'cod') {
            return $this->registry->get('language')->get('cdek_shipping__payment_type_cod');
        }

        return $this->registry->get('language')->get('cdek_shipping__payment_type_online');
    }
}
