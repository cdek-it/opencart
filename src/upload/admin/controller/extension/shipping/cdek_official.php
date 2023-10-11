<?php

require_once(DIR_SYSTEM . 'library/cdek_official/vendor/autoload.php');

use CDEK\App;
use CDEK\CdekApi;
use CDEK\CdekOrderMetaRepository;
use CDEK\Settings;

class ControllerExtensionShippingCdekOfficial extends Controller
{
    public function index()
    {
        $this->load->language('extension/shipping/cdek_official');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        $data = [];
        $registry = $this->registry;
        $app = new App($registry, $data, DIR_APPLICATION);
        $app->handleAjaxRequest();
        $app->run();

        $app->checkState($app->data);
        $userToken = $this->session->data['user_token'];
        $app->data['action'] = $this->url->link('extension/shipping/cdek_official', 'user_token=' . $userToken);
        $app->data['cancel'] = $this->url->link('extension/shipping', 'user_token=' . $userToken);
        $app->data['header'] = $this->load->controller('common/header');
        $app->data['column_left'] = $this->load->controller('common/column_left');
        $app->data['footer'] = $this->load->controller('common/footer');
        $app->data['user_token'] = $userToken;
        if ($app->data['status_auth']) {
            $app->data['city'] = $app->data['map_city'] ?? 'Москва';
        } else {
            $app->data['city'] = 'Москва';
        }
        $app->data['apikey'] = $app->settings->authSettings->apiKey;

        $this->response->setOutput($this->load->view('extension/shipping/cdek_official', $app->data));
    }

    public function cdek_official_order_info(&$route, &$data, &$output)
    {
        $orderId = (int)$data['order_id'];
        if ($this->isCdekShipping($orderId)) {
            $remoteDelete = false;
            $invalidOrder = false;
            $dataOrderForm['cdek_order_deleted'] = false;
            $dataOrderForm['cdek_order_created'] = false;
            $dataOrderForm['order_id'] = $orderId;
            $orderDeleted = CdekOrderMetaRepository::isOrderDeleted($this->db, $orderId);
            $orderCreated = CdekOrderMetaRepository::isOrderCreated($this->db, $orderId);

            //created
            if ($orderCreated['created'] && !$orderDeleted['deleted']) {
                $orderMetaData = $orderCreated['row'];
                $settings = new Settings();
                $settings->init($this->model_setting_setting->getSetting('cdek_official'));
                $cdekApi = new CdekApi($this->registry, $settings);
                $order = $cdekApi->getOrderByUuid($orderMetaData['cdek_uuid']);
                if ($order->requests[0]->state === 'INVALID') {
                    $errorsCode = [];
                    foreach ($order->requests[0]->errors as $errors) {
                        $errorsCode[$errors->code] = $errors->message;
                    }
                    if (in_array('v2_entity_not_found', array_keys($errorsCode))) {
                        CdekOrderMetaRepository::deleteOrder($this->db, $orderId);
                        $remoteDelete = true;
                    } else {
                        $invalidOrder = true;
                    }
                }

                if (!$remoteDelete && !$invalidOrder) {
                    $dataOrderForm['cdek_order_created'] = true;
                    $dataOrderForm['cdek_order_deleted'] = false;
                    $dataOrderForm['products'] = $data['products'];
                    if ($orderMetaData['cdek_number'] === "") {
                        $param = [
                            'cdek_number' => $order->entity->cdek_number,
                            'cdek_uuid' => $orderMetaData['cdek_uuid'],
                            'name' => $order->entity->recipient->name,
                            'type' => $this->getDeliveryModeName($order->entity->delivery_mode),
                            'payment_type' => $orderMetaData['payment_type'],
                            'to_location' => $order->entity->to_location->city . ', ' . $order->entity->to_location->address,
                            'pvz_code' => $order->entity->shipment_point ?? ''
                        ];
                        CdekOrderMetaRepository::insertOrderMeta($this->db, $param, $dataOrderForm['order_id']);
                        $orderMetaData = CdekOrderMetaRepository::getOrder($this->db, $orderId);
                    }
                    $dataOrderForm = array_merge($dataOrderForm, $orderMetaData);
                }
            }

            //deleted
            if (!$invalidOrder) {
                if ((!$orderCreated['created'] && $orderDeleted['deleted']) || $remoteDelete) {
                    $dataOrderForm['cdek_order_deleted'] = true;
                    $dataOrderForm['cdek_order_created'] = false;
                    $data = CdekOrderMetaRepository::getOrder($this->db, $orderId);
                    $dataOrderForm = array_merge($dataOrderForm, $data->rows[0]);
                }
            } else {
                $dataOrderForm['cdek_order_error_create_message'] = array_values($errorsCode)[0];
            }

            $this->displayCreateOrderForm($output, $dataOrderForm);
        }
    }

    public function install()
    {
        $this->load->model('setting/setting');
        $data['shipping_cdek_official_status'] = 1;
        $this->model_setting_setting->editSetting('shipping_cdek_official', $data);
        $this->log->write('install start');
        $this->load->model('extension/shipping/cdek_official');
        $this->model_extension_shipping_cdek_official->createEvents();
        CdekOrderMetaRepository::create($this->db, DB_PREFIX);
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $data['shipping_cdek_official_status'] = 0;
        $this->model_setting_setting->editSetting('shipping_cdek_official', $data);
        $this->load->model('extension/shipping/cdek_official');
        $this->model_extension_shipping_cdek_official->deleteEvents();
    }

    private function getDeliveryModeName(int $deliveryMode)
    {
        if (in_array($deliveryMode, [1, 3, 8])) {
            return $this->language->get('cdek_shipping__tariff_type_to_door');
        }
        return $this->language->get('cdek_shipping__tariff_type_to_warehouse');
    }

    protected function displayCreateOrderForm(&$output, $data)
    {
        $this->load->language('extension/shipping/cdek_official');
        $scriptPath = DIR_APPLICATION . 'view/javascript/cdek_official/create_order.js';
        $data['create_order_js'] = file_exists($scriptPath) ? file_get_contents($scriptPath) : '';
        $stylePath = DIR_APPLICATION . 'view/stylesheet/cdek_official/create_order.css';
        $data['create_order_style'] = file_exists($stylePath) ? file_get_contents($stylePath) : '';
        $data['user_token'] = $this->session->data['user_token'];
        $customContent = $this->load->view('extension/shipping/cdek_official_create_order', $data);
        $search = '<div class="panel panel-default">';
        $replace = $customContent . $search;

        $offset = 0;
        $count = 0;
        $limit = 5;

        while (($pos = strpos($output, $search, $offset)) !== false) {
            $count++;
            $offset = $pos + 1;
            if ($count === $limit) {
                $output = substr_replace($output, $replace, $pos, strlen($search));
                break;
            }
        }
    }

    protected function isCdekShipping(int $orderId)
    {
        $orderOC = $this->model_sale_order->getOrder($orderId);
        $shippingCode = explode('.', $orderOC['shipping_code'])[0];
        if ($shippingCode === 'cdek_official') {
            return true;
        }
        return false;
    }
}