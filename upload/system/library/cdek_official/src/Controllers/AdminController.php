<?php

namespace CDEK\Controllers;

use CDEK\Actions\Admin\Installer\InstallExtensionAction;
use CDEK\Actions\Admin\Installer\RemoveExtensionAction;
use CDEK\Actions\Admin\Order\GetOrderInfoScriptsAction;
use CDEK\Actions\Admin\Order\GetOrderInfoTabAction;
use CDEK\Actions\Admin\Settings\GetOfficesAction;
use CDEK\Actions\Admin\Settings\RenderSettingsPageAction;
use CDEK\Actions\Admin\Settings\SaveSettingsAction;
use CDEK\CdekApi;
use CDEK\CdekHelper;
use CDEK\Helpers\DeliveryCalculator;
use CDEK\OrderMetaRepository;
use CDEK\Contracts\ControllerContract;
use CDEK\RegistrySingleton;
use CDEK\SettingsSingleton;
use Exception;

class AdminController extends ControllerContract
{

    final public function uninstall(): void
    {
        (new RemoveExtensionAction)();
    }

    /**
     * @throws Exception
     */
    final public function index(): void
    {
        (new RenderSettingsPageAction)();
    }

    final public function store(): void
    {
        (new SaveSettingsAction)();
    }

    final public function map(): void
    {
        (new GetOfficesAction)();
    }

    /**
     * @noinspection PhpUnused
     */
    final public function orderInfoScripts(): void
    {
        (new GetOrderInfoScriptsAction)();
    }

    /**
     * @throws Exception
     * @noinspection PhpUnused
     */
    final public function orderInfo(string &$route, array &$data): void
    {
        $data['tabs'][] = (new GetOrderInfoTabAction)((int)$this->registry->get('request')->get['order_id']);
        return;

        $orderId = (int)$data['order_id'];
        if ($this->isCdekShipping($orderId)) {
            $remoteDelete                        = false;
            $invalidOrder                        = false;
            $dataOrderForm['cdek_order_deleted'] = false;
            $dataOrderForm['cdek_order_created'] = false;
            $dataOrderForm['order_id']           = $orderId;
            $orderDeleted                        = OrderMetaRepository::isOrderDeleted($orderId);
            $orderCreated                        = OrderMetaRepository::isOrderCreated($orderId);

            //created
            if ($orderCreated['created'] && !$orderDeleted['deleted']) {
                $orderMetaData = $orderCreated['row'];
                $settings      = new SettingsSingleton;
                $settings->init($this->model_setting_setting->getSetting('cdek_official'));
                $cdekApi = new CdekApi($settings);
                $order   = $cdekApi->getOrderByUuid($orderMetaData['cdek_uuid']);
                if ($order->requests[0]->state === 'INVALID') {
                    $errorsCode = [];
                    foreach ($order->requests[0]->errors as $errors) {
                        $errorsCode[$errors->code] = $errors->message;
                    }
                    if (array_key_exists('v2_entity_not_found', $errorsCode)) {
                        OrderMetaRepository::deleteOrder($orderId);
                        $remoteDelete = true;
                    } else {
                        $invalidOrder = true;
                    }
                }

                if (!$remoteDelete && !$invalidOrder) {
                    $dataOrderForm['cdek_order_created'] = true;
                    $dataOrderForm['cdek_order_deleted'] = false;
                    $dataOrderForm['products']           = $data['products'];
                    if ($orderMetaData['cdek_number'] === "") {
                        $param = [
                            'cdek_number'  => $order->entity->cdek_number,
                            'cdek_uuid'    => $orderMetaData['cdek_uuid'],
                            'name'         => $order->entity->recipient->name,
                            'type'         => $this->getDeliveryModeName($order->entity->delivery_mode),
                            'payment_type' => $orderMetaData['payment_type'],
                            'to_location'  => $order->entity->to_location->city .
                                              ', ' .
                                              $order->entity->to_location->address,
                            'pvz_code'     => $order->entity->shipment_point ?? '',
                        ];
                        OrderMetaRepository::insertOrderMeta($param, $dataOrderForm['order_id']);
                        $orderMetaData = OrderMetaRepository::getOrder($this->db, $orderId);
                    }
                    $dataOrderForm = array_merge($dataOrderForm, $orderMetaData);
                }
            }

            //deleted
            if (!$invalidOrder) {
                if ((!$orderCreated['created'] && $orderDeleted['deleted']) || $remoteDelete) {
                    $dataOrderForm['cdek_order_deleted'] = true;
                    $dataOrderForm['cdek_order_created'] = false;
                    $data                                = OrderMetaRepository::getOrder($this->db, $orderId);
                    $dataOrderForm                       = array_merge($dataOrderForm, $data->rows[0]);
                }
            } else {
                $dataOrderForm['cdek_order_error_create_message'] = array_values($errorsCode)[0];
            }

            $recommendedDimensions = $this->getRecommendedPackage($orderId);
            $orderMeta             = OrderMetaRepository::getOrder($this->db, $orderId);
            $this->load->model('sale/order');
            $dataOrderForm['order_direction'] = CdekHelper::getTariffDirectionByOrderId($this->model_sale_order,
                                                                                        $orderId);
            $dataOrderForm['pvz_code_info']   = $orderMeta->rows[0]['pvz_code'] ?? null;
            $dataOrderForm                    = array_merge($dataOrderForm, $recommendedDimensions);

            $this->displayCreateOrderForm($output, $dataOrderForm);
        }
    }

    protected function isCdekShipping(int $orderId)
    {
        $this->load->model('sale/order');
        $orderOC      = $this->model_sale_order->getOrder($orderId);
        $shippingCode = explode('.', $orderOC['shipping_code'])[0];

        return $shippingCode === 'cdek_official';
    }

    private function getDeliveryModeName(int $deliveryMode)
    {
        if (in_array($deliveryMode, [1, 3, 8])) {
            return $this->language->get('cdek_shipping__tariff_type_to_door');
        }
        return $this->language->get('cdek_shipping__tariff_type_to_warehouse');
    }

    protected function getRecommendedPackage(int $orderId)
    {
        $this->load->model('sale/order');
        $this->load->model('catalog/product');
        $products        = $this->model_sale_order->getOrderProducts($orderId);
        foreach ($products as $key => $product) {
            $productOC          = $this->model_catalog_product->getProduct($product['product_id']);
            $productsPackages[] = [
                'length'   => (int)$productOC['length'],
                'width'    => (int)$productOC['width'],
                'height'   => (int)$productOC['height'],
                'weight'   => (int)$productOC['weight'],
                'quantity' => (int)$products[$key]['quantity'],
            ];
        }
        return DeliveryCalculator::getRecommendedPackage($productsPackages);
    }

    protected function displayCreateOrderForm(&$output, $data)
    {
        $this->load->language('extension/shipping/cdek_official');
        $scriptPath                 = DIR_APPLICATION . 'view/javascript/cdek_official/create_order.js';
        $data['create_order_js']    = file_exists($scriptPath) ? file_get_contents($scriptPath) : '';
        $stylePath                  = DIR_APPLICATION . 'view/stylesheet/cdek_official/create_order.css';
        $data['create_order_style'] = file_exists($stylePath) ? file_get_contents($stylePath) : '';
        $data['user_token']         = $this->session->data['user_token'];
        $customContent              = $this->load->view('extension/shipping/cdek_official_create_order', $data);
        $search                     = '<div class="panel panel-default">';
        $replace                    = $customContent . $search;

        $offset = 0;
        $count  = 0;
        $limit  = 5;

        while (($pos = strpos($output, $search, $offset)) !== false) {
            $count++;
            $offset = $pos + 1;
            if ($count === $limit) {
                $output = substr_replace($output, $replace, $pos, strlen($search));
                break;
            }
        }
    }

    final public function install(): void
    {
        (new InstallExtensionAction)();
    }
}
