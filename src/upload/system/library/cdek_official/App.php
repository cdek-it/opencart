<?php

require_once(DIR_SYSTEM . 'library/cdek_official/Settings.php');
require_once(DIR_SYSTEM . 'library/cdek_official/CdekApi.php');
require_once(DIR_SYSTEM . 'library/cdek_official/model/Order.php');
require_once(DIR_SYSTEM . 'library/cdek_official/test/CdekTest.php');
require_once(DIR_SYSTEM . 'library/cdek_official/CdekApiValidate.php');

class App
{
    public Registry $registry;
    public array $data;
    public string $dirApplication;
    public $url;
    public $language;
    public $session;
    public $requestMethod;
    public $userToken;
    public CdekApi $cdekApi;
    public $settings;
    public $modelSetting;
    public $request;
    private $load;
    private $db;

    public function __construct(Registry $registry, array $data, string $dirApplication)
    {
        $this->registry = $registry;
        $this->data = $data;
        $this->dirApplication = $dirApplication;
        $this->session = $this->registry->get('session');
        $this->load = $this->registry->get('load');
        $this->language = $this->registry->get('language');
        $this->url = $this->registry->get('url');
        $this->request = $this->registry->get('request');
        $this->requestMethod = $this->request->server['REQUEST_METHOD'] ?? '';
        $this->userToken = $this->session->data['user_token'] ?? '';
        $this->db = $this->registry->get('db');
//        $this->registry->get('model_setting_setting')->getSetting('cdek_official');
        $this->modelSetting = $this->registry->get('model_setting_setting');
        $this->settings = new Settings();
        $this->cdekApi = new CdekApi($registry, $this->settings);
    }

    public function run(): void
    {
        $this->init();
        $this->connectScripts();
        $this->checkError();
        $this->breadcrumbs();
    }

    public function connectScripts(): void
    {
        $scriptPath = $this->dirApplication . 'view/javascript/cdek_official/settings_page.js';
        $this->data['settings_page'] = file_exists($scriptPath) ? file_get_contents($scriptPath) : '';

        $stylePath = $this->dirApplication . 'view/stylesheet/cdek_official/settings_page.css';
        $this->data['settings_page_style'] = file_exists($stylePath) ? file_get_contents($stylePath) : '';
    }

    public function checkError(): void
    {
        $this->data['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->data['error_warning'] = $this->session->data['error_warning'] ?? '';
        unset($this->session->data['error_warning']);
    }

    public function init(): void
    {
        if ($this->requestMethod === 'POST') {
            $postSettings = $this->request->post;
            $this->settings->init($postSettings);
            $this->data['status_auth'] = $this->cdekApi->checkAuth();
            $this->modelSetting->editSetting('cdek_official', $postSettings);
            try {
                $this->settings->validate();
                $this->session->data['success'] = $this->language->get('text_success');
            } catch (Exception $exception) {
                $this->registry->get('log')->write(">CDEK_OFFICIAL_LOG Validation failed: " . $this->language->get($exception->getMessage()));
                $this->session->data['error_warning'] = $this->language->get('error_permission') .
                    $this->language->get($exception->getMessage());
            }

            $redirectUrl = $this->url->link('extension/shipping/cdek_official', "user_token={$this->userToken}");
            $this->registry->get('response')->redirect($redirectUrl);
        }

        $modelSettings = $this->modelSetting->getSetting('cdek_official');
        if (!empty($modelSettings)) {

            $this->settings->init($this->modelSetting->getSetting('cdek_official'));
            $this->settings->updateData($this->data);

            $this->data['status_auth'] = $this->cdekApi->checkAuth();
            $this->data['tariffs'] = $this->settings->shippingSettings->shippingTariffs;
            $this->data['currencies'] = $this->settings->shippingSettings->shippingCurrencies;
        } else {
            $this->data['status_auth'] = false;
        }


//        $cdekTest = new CdekTest($this->controller);
//        $cdekTest->test();

//        try {
//            $this->data['status_auth'] = $this->cdekApi->checkAuth();
//            if ($this->data['status_auth']) {
//                $settings->validate();
//            }
////            $cdekTest = new CdekTest($this->controller);
////            $cdekTest->test();
//        } catch (Exception $exception) {
//            $this->session->data['error_warning'] = $this->language->get('error_permission') .
//                $this->language->get($exception->getMessage());
//        }
    }

    public function breadcrumbs(): void
    {
        $this->data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', "user_token={$this->userToken}", true)
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('marketplace/extension', "user_token={$this->userToken}&type=shipping", true)
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/shipping/cdek_official', "user_token={$this->userToken}", true)
            ]
        ];
    }

    public function handleAjaxRequest()
    {
        $this->settings->init($this->modelSetting->getSetting('cdek_official'));
        $cdekApiValidate = new CdekApiValidate();
        if (isset($this->request->post['cdekRequest'])) {

            if ($this->request->post['cdekRequest'] === 'getCity') {
                if ($this->request->post['key'] === '') {
                    exit;
                }
                $result = $this->cdekApi->getCity($this->request->post['key']);
                echo json_encode($result);
                exit;
            }

            if ($this->request->post['cdekRequest'] === 'getPvz') {
                if ($this->request->post['key'] === '') {
                    exit;
                }
                $result = $this->cdekApi->getPvz($this->request->post['key'], $this->request->post['street']);
                echo json_encode($result);
                exit;
            }

            if ($this->request->post['cdekRequest'] === 'createOrder') {
                $validate = $this->validateCreateOrderRequest($this->request->post['dimensions'], $this->request->post['order_id']);
                if (!$validate['state']) {
                    echo json_encode($validate);
                    exit;
                }
                $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cdek_order_meta` WHERE `order_id` = " . (int)$this->request->post['order_id']);
                $pvz = '';
                if ($query->num_rows && $query->row['pvz_code'] !== "") {
                    $pvz = $query->row['pvz_code'];
                }
                $this->registry->get('load')->model('sale/order');
                $model_sale_order = $this->registry->get('model_sale_order');
                $this->registry->get('load')->model('catalog/product');
                $model_catalog_product = $this->registry->get('model_catalog_product');
                $weight = $this->registry->get('weight');
                $orderId = (int)$this->request->post['order_id'];
                $orderOC = $model_sale_order->getOrder($orderId);
                $products = $model_sale_order->getOrderProducts($orderId);
                $order = new Order($this->settings, $orderOC, $products, $this->request->post['dimensions'], $model_catalog_product, $weight, $pvz);
                $response = $this->cdekApi->createOrder($order);
                file_put_contents('test_log.txt', "Order created: " . json_encode($response) . "\n", FILE_APPEND);
                if ($cdekApiValidate->createApiValidate($response)) {
                    sleep(5);
                    $order = $this->cdekApi->getOrderByUuid($response->entity->uuid);
                    $data = [
                        'cdek_number' => $order->entity->cdek_number ?? $this->language->get('cdek_error_cdek_number_empty'),
                        'cdek_uuid' => $order->entity->uuid,
                        'name' => $order->entity->recipient->name,
                        'type' => isset($order->entity->delivery_mode) ? $this->getDeliveryModeName((int) $order->entity->delivery_mode) : null,
                        'payment_type' => $this->getPaymentTypeName($orderOC['payment_code']),
                        'to_location' => $order->entity->to_location->city ?? '' . ', ' . $order->entity->to_location->address
                    ];
                    $this->insertOrderMeta($data, $orderId);
                    file_put_contents('test_log.txt', "Order validated" . "\n", FILE_APPEND);
                    echo json_encode([
                        'state' => true,
                        'data' => $data
                    ]);
                } else {
                    file_put_contents('test_log.txt', "Order not validated" . "\n", FILE_APPEND);
                    echo json_encode(['state' => false, 'message' => 'Order wrong']);
                }
                exit;
            }

            if ($this->request->post['cdekRequest'] === 'deleteOrder') {
                $response = $this->cdekApi->deleteOrder($this->request->post['uuid']);
                if ($response->requests[0]->state === 'ACCEPTED' || $response->requests[0]->state === 'SUCCESSFUL') {
                    $this->db->query("DELETE FROM `" . DB_PREFIX . "cdek_order_meta` WHERE `order_id` = " . (int)$this->request->post['order_id']);
                    echo json_encode([
                        'state' => true,
                        'message' => ''
                    ]);
                } else {
                    echo json_encode([
                        'state' => false,
                        'message' => 'The order could not be deleted'
                    ]);
                }
                exit;
            }
        }
    }

    public function checkState(&$data)
    {
        $data['shipping_cdek_official_status'] = 1;
        $this->modelSetting->editSetting('shipping_cdek_official', $data);
        file_put_contents('test_log.txt', "Response: " . json_encode($this->settings) . "\n", FILE_APPEND);
    }

    private function validateCreateOrderRequest($dimensions, $orderId): array
    {
        if (empty($dimensions['length'])) {
            return ['state' => false, 'message' => $this->language->get('cdek_error_dimensions_length_empty')];
        }

        if (empty($dimensions['width'])) {
            return ['state' => false, 'message' => $this->language->get('cdek_error_dimensions_width_empty')];
        }

        if (empty($dimensions['height'])) {
            return ['state' => false, 'message' => $this->language->get('cdek_error_dimensions_height_empty')];
        }

        if (empty($orderId)) {
            return ['state' => false, 'message' => $this->language->get('cdek_error_dimensions_order_id_empty')];
        }

        return ['state' => true, 'message' => ''];
    }

    private function getDeliveryModeName(int $deliveryMode)
    {
        if (in_array($deliveryMode, [1, 3, 8])) {
            return $this->language->get('cdek_shipping__tariff_type_to_door');
        }
        return $this->language->get('cdek_shipping__tariff_type_to_warehouse');
    }

    private function getPaymentTypeName($paymentCode)
    {
        if ($paymentCode === 'cod') {
            return $this->language->get('cdek_shipping__payment_type_cod');
        }
        return $this->language->get('cdek_shipping__payment_type_online');
    }

    private function insertOrderMeta(array $data, int $orderId)
    {
        if (!is_numeric($data['cdek_number'])) {
            $data['cdek_number'] = null;
        }

        $this->db->query(
            "INSERT INTO oc_cdek_order_meta SET order_id = " . $orderId
            . ", cdek_number = '" . $this->db->escape($data['cdek_number']) . "'"
            . ", cdek_uuid = '" . $this->db->escape($data['cdek_uuid']) . "'"
            . ", name = '" . $this->db->escape($data['name']) . "'"
            . ", type = '" . $this->db->escape($data['type']) . "'"
            . ", payment_type = '" . $this->db->escape($data['payment_type']) . "'"
            . ", to_location = '" . $this->db->escape($data['to_location']) . "'"
            . " ON DUPLICATE KEY UPDATE "
            . "cdek_number = VALUES(cdek_number), "
            . "cdek_uuid = VALUES(cdek_uuid), "
            . "name = VALUES(name), "
            . "type = VALUES(type), "
            . "payment_type = VALUES(payment_type), "
            . "to_location = VALUES(to_location)"
        );
    }
}