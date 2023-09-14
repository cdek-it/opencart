<?php

require_once(DIR_SYSTEM . 'library/cdek_official/Settings.php');
require_once(DIR_SYSTEM . 'library/cdek_official/CdekApi.php');
require_once(DIR_SYSTEM . 'library/cdek_official/test/CdekTest.php');

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

    public function __construct(Registry $registry, array $data, string $dirApplication)
    {
        $this->registry = $registry;
        $this->data = $data;
        $this->dirApplication = $dirApplication;
        $this->session = $this->registry->get('session');
        $this->language = $this->registry->get('language');
        $this->url = $this->registry->get('url');
        $this->request = $this->registry->get('request');
        $this->requestMethod = $this->request->server['REQUEST_METHOD'] ?? '';
        $this->userToken = $this->session->data['user_token'] ?? '';
//        $this->registry->get('model_setting_setting')->getSetting('cdek_official');
        $this->modelSetting = $this->registry->get('model_setting_setting');
        $this->settings = new Settings();
        $this->cdekApi = new CdekApi($registry, $this->settings);
    }

    public function run():void
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
                $this->registry->log->write(">CDEK_OFFICIAL_LOG Validation failed: " . $this->language->get($exception->getMessage()));
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
        }
    }

    public function checkState(&$data)
    {
        $data['shipping_cdek_official_status'] = 1;
        $this->modelSetting->editSetting('shipping_cdek_official', $data);
        file_put_contents('test_log.txt', "Response: " . json_encode($this->settings) . "\n", FILE_APPEND);
    }
}