<?php

namespace CDEK;

use CDEK\Helpers\LogHelper;
use CDEK\Models\Tariffs;
use Exception;
use Registry;

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

    public function __construct(array $data, string $dirApplication)
    {
        $this->registry = RegistrySingleton::getInstance();

        $this->data           = $data;
        $this->dirApplication = $dirApplication;
        $this->session        = $this->registry->get('session');
        $this->load           = $this->registry->get('load');
        $this->language       = $this->registry->get('language');
        $this->url            = $this->registry->get('url');
        $this->request        = $this->registry->get('request');
        $this->requestMethod  = $this->request->server['REQUEST_METHOD'] ?? '';
        $this->userToken      = $this->session->data['user_token'] ?? '';
        $this->db             = $this->registry->get('db');
        $this->load->model('setting/setting');
        $this->modelSetting = $this->registry->get('model_setting_setting');
        $this->settings     = new Settings;
        $this->cdekApi      = new CdekApi($this->settings);
    }

    public function run(): void
    {
        $this->init();
        $this->checkError();
    }

    public function init(): void
    {
        if ($this->requestMethod === 'POST') {
            $postSettings = $this->request->post;
            $this->settings->init($postSettings);
            $this->settings->updateData($this->data);
            $this->modelSetting->editSetting('cdek_official', $postSettings);
            $isAuth                    = $this->cdekApi->checkAuth();
            $this->data['status_auth'] = $isAuth;
            if (!$isAuth) {
                $redirectUrl = $this->url->link('extension/shipping/cdek_official',
                                                "user_token=$this->userToken",
                                                true);
                $this->registry->get('response')->redirect($redirectUrl);
            }
            try {
                $this->settings->validate();
                $this->session->data['success'] = $this->language->get('text_success');
            } catch (Exception $exception) {
                LogHelper::write('Validation failed: ' .
                                 $this->language->get($exception->getMessage()));
                $this->session->data['error_warning'] = $this->language->get('error_permission') .
                                                        $this->language->get($exception->getMessage());
            }

            $redirectUrl = $this->url->link('extension/shipping/cdek_official', "user_token=$this->userToken", true);
            $this->registry->get('response')->redirect($redirectUrl);
        }

        $this->settings->init($this->modelSetting->getSetting('cdek_official'));
        $isAuth                    = $this->cdekApi->checkAuth();
        $this->data['status_auth'] = $isAuth;
        $this->settings->updateData($this->data);

        $this->data['map_city'] = 'Москва';
        if (!empty($this->settings->shippingSettings->shippingPvz)) {
            $locality = CdekHelper::getLocality($this->settings->shippingSettings->shippingPvz);
            if (CdekHelper::hasLocalityCity($locality)) {
                $this->data['map_city'] = $locality->city;
            }
        } elseif (!empty($this->settings->shippingSettings->shippingCityAddress)) {
            $locality = CdekHelper::getLocality($this->settings->shippingSettings->shippingCityAddress);
            if (CdekHelper::hasLocalityCity($locality)) {
                $this->data['map_city'] = $locality->city;
            }
        }

        $this->data['tariffs']        = Tariffs::getTariffList();
        $this->data['enabledTariffs'] = $this->settings->shippingSettings->enabledTariffs;
        $this->data['currencies']     = $this->settings->shippingSettings->shippingCurrencies;
    }

    public function checkError(): void
    {
        $this->data['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        $this->data['error_warning'] = $this->session->data['error_warning'] ?? '';
        unset($this->session->data['error_warning']);
    }

    public function handleAjaxRequest()
    {
        if (!isset($this->request->post['cdekRequest']) && !isset($this->request->get['cdekRequest'])) {
            return;
        }
        $requestAction = $this->request->post['cdekRequest'] ?? $this->request->get['cdekRequest'];

        $this->settings->init($this->modelSetting->getSetting('cdek_official'));

        if ($requestAction === 'createOrder') {
            $createOrder = new CreateOrder($this->registry, $this->settings, $this->cdekApi);
            $createOrder->create();
        }

        if ($requestAction === 'deleteOrder') {
            $order = CdekOrderMetaRepository::getOrder($this->db, (int)$this->request->post['order_id']);
            CdekOrderMetaRepository::deleteOrder((int)$this->request->post['order_id']);
            $response = $this->cdekApi->deleteOrder($this->request->post['uuid']);
            if (CdekApiValidate::deleteOrder($response)) {
                $message = 'Order successfully deleted';
                $state   = true;
            } else {
                $state = false;
                if ($response->requests[0]->errors[0]->code === 'v2_entity_invalid') {
                    $orderResponse = $this->cdekApi->getOrderByUuid($this->request->post['uuid']);
                    $message       = $orderResponse->requests[0]->errors[0]->message;
                } else {
                    $message = 'An error occurred during deletion. The order was marked as deleted. Error code: ' .
                               $response->requests[0]->errors[0]->code .
                               '. Contact the technical support of the module';
                }
            }
            echo json_encode(['state' => $state, 'message' => $message, 'order' => $order->rows[0]]);
            exit;
        }

        if ($requestAction === 'getBill') {
            $this->cdekApi->getBill($this->request->get['uuid']);
        }
    }

    public function checkState(&$data)
    {
        $data['shipping_cdek_official_status'] = 1;
        $this->modelSetting->editSetting('shipping_cdek_official', $data);
        file_put_contents('test_log.txt', "Response: " . json_encode($this->settings) . "\n", FILE_APPEND);
    }
}
