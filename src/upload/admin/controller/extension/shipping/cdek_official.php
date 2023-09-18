<?php

require_once(DIR_SYSTEM . 'library/cdek_official/App.php');

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

        $authId = $app->settings->authSettings->authId;
        $authSecret = $app->settings->authSettings->authSecret;
        $servicePhp = file_get_contents(DIR_SYSTEM . 'library/cdek_official/service.php');
        $servicePhp = str_replace("{{AUTH_ID}}", $authId, $servicePhp);
        $servicePhp = str_replace("{{AUTH_SECRET}}", $authSecret, $servicePhp);
        file_put_contents('../service.php', $servicePhp);

        $app->checkState($app->data);

        $userToken = $this->session->data['user_token'];
        $app->data['action'] = $this->url->link('extension/shipping/cdek_official', 'user_token=' . $userToken);
        $app->data['cancel'] = $this->url->link('extension/shipping', 'user_token=' . $userToken);
        $app->data['header'] = $this->load->controller('common/header');
        $app->data['column_left'] = $this->load->controller('common/column_left');
        $app->data['footer'] = $this->load->controller('common/footer');
        $app->data['user_token'] = $userToken;

        $this->response->setOutput($this->load->view('extension/shipping/cdek_official', $app->data));
    }

    public function cdek_official_order_info(&$route, &$data, &$output)
    {
        $this->log->write('event start');
        $this->load->language('extension/shipping/cdek_official');
        $scriptPath = DIR_APPLICATION . 'view/javascript/cdek_official/create_order.js';
        $dataOrderForm['create_order_js'] = file_exists($scriptPath) ? file_get_contents($scriptPath) : '';
        $dataOrderForm['user_token'] = $this->session->data['user_token'];
        $dataOrderForm['order_id'] = $data['order_id'];
        $dataOrderForm['products'] = $data['products'];
        $dataOrderForm['cdek_order_create_info_name'] = $this->language->get('cdek_order_create_info_name');
        $dataOrderForm['cdek_order_number_name'] = $this->language->get('cdek_order_number_name');
        $dataOrderForm['cdek_order_customer_name'] = $this->language->get('cdek_order_customer_name');
        $dataOrderForm['cdek_order_type_name'] = $this->language->get('cdek_order_type_name');
        $dataOrderForm['cdek_order_payment_type_name'] = $this->language->get('cdek_order_payment_type_name');
        $dataOrderForm['cdek_order_direction_name'] = $this->language->get('cdek_order_direction_name');
        $dataOrderForm['cdek_order_get_bill_name'] = $this->language->get('cdek_order_get_bill_name');
        $dataOrderForm['cdek_order_call_courier_name'] = $this->language->get('cdek_order_call_courier_name');
        $dataOrderForm['cdek_order_delete_order_name'] = $this->language->get('cdek_order_delete_order_name');
        $dataOrderForm['cdek_order_created'] = false;

        $stylePath = $this->dirApplication . 'view/stylesheet/cdek_official/create_order.css';
        $dataOrderForm['create_order_style'] = file_exists($stylePath) ? file_get_contents($stylePath) : '';

        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "cdek_order_meta` WHERE `order_id` = " . (int)$data['order_id']);
        if ($query->num_rows) {
            $dataOrderForm['cdek_order_created'] = true;
            $orderMetaData = $query->row;
            $dataOrderForm['cdek_number'] = $orderMetaData['cdek_number'];
            $dataOrderForm['cdek_uuid'] = $orderMetaData['cdek_uuid'];
            $dataOrderForm['name'] = $orderMetaData['name'];
            $dataOrderForm['type'] = $orderMetaData['type'];
            $dataOrderForm['payment_type'] = $orderMetaData['payment_type'];
            $dataOrderForm['to_location'] = $orderMetaData['to_location'];
        }

        $customContent = $this->load->view('extension/shipping/cdek_official_create_order', $dataOrderForm);

        $search = '<div class="panel panel-default">';
        $replace = $search . $customContent;

        $offset = 0;
        $count = 0;
        $limit = 4;

        while (($pos = strpos($output, $search, $offset)) !== false) {
            $count++;
            $offset = $pos + 1;
            if ($count === $limit) {
                $output = substr_replace($output, $replace, $pos, strlen($search));
                break;
            }
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

        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cdek_order_meta` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `order_id` INT(11) NOT NULL,
                `cdek_number` VARCHAR(255) NOT NULL,
                `cdek_uuid` VARCHAR(255) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `type` VARCHAR(255) NOT NULL,
                `payment_type` VARCHAR(255) NOT NULL,
                `to_location` VARCHAR(255) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `order_id_unique` (`order_id`),
                FOREIGN KEY (`order_id`) REFERENCES `" . DB_PREFIX . "order`(`order_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
        ");
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $data['shipping_cdek_official_status'] = 0;
        $this->model_setting_setting->editSetting('shipping_cdek_official', $data);
        $this->load->model('extension/shipping/cdek_official');
        $this->model_extension_shipping_cdek_official->deleteEvents();
    }
}