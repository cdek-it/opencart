<?php

require_once(DIR_SYSTEM . 'library/cdek_official/Settings.php');
require_once(DIR_SYSTEM . 'library/cdek_official/CdekApi.php');
require_once(DIR_SYSTEM . 'library/cdek_official/test/CdekTest.php');

class App
{
    private Controller $controller;
    public array $data;
    private string $dirApplication;
    private $url;
    private $language;
    private $session;
    private $requestMethod;
    private $userToken;

    public function __construct(Controller $controller, array $data, string $dirApplication)
    {
        $this->controller = $controller;
        $this->data = $data;
        $this->dirApplication = $dirApplication;
        $this->session = $this->controller->session;
        $this->language = $this->controller->language;
        $this->url = $this->controller->url;
        $this->requestMethod = $this->controller->request->server['REQUEST_METHOD'] ?? '';
        $this->userToken = $this->controller->session->data['user_token'] ?? '';
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
        $settings = new Settings();

        if ($this->requestMethod === 'POST') {
            $postSettings = $this->controller->request->post;
            $settings->init($postSettings);

            try {
                $this->controller->model_setting_setting->editSetting('cdek_official', $postSettings);
                $settings->validate();
                $this->session->data['success'] = $this->language->get('text_success');
            } catch (Exception $exception) {
                $this->session->data['error_warning'] = $this->language->get('error_permission') .
                    $this->language->get($exception->getMessage());
            }

            $redirectUrl = $this->url->link('extension/shipping/cdek_official', "user_token={$this->userToken}");
            $this->controller->response->redirect($redirectUrl);
        }

        $settings->init($this->controller->model_setting_setting->getSetting('cdek_official'));
        $settings->updateData($this->data);

        try {
            $settings->validate();
            $cdekApi = new CdekApi($this->controller);
            $this->data['status_auth'] = $cdekApi->checkAuth();
            $cdekTest = new CdekTest($this->controller);
            $cdekTest->test();
        } catch (Exception $exception) {
            $this->session->data['error_warning'] = $this->language->get('error_permission') .
                $this->language->get($exception->getMessage());
        }

        $this->data['tariffs'] = $settings->shippingSettings->shippingTariffs;
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
}