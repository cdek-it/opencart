<?php

require_once(DIR_SYSTEM . 'library/cdek_official/Settings.php');
require_once(DIR_SYSTEM . 'library/cdek_official/CdekApi.php');
require_once(DIR_SYSTEM . 'library/cdek_official/test/CdekTest.php');

class ControllerExtensionShippingCdekOfficial extends Controller
{
    public function index()
    {
        $this->load->language('extension/shipping/cdek_official');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        $script_path = DIR_APPLICATION . 'view/javascript/cdek_official/settings_page.js';
        if (file_exists($script_path)) {
            $script_content = file_get_contents($script_path);
            $data['settings_page'] = $script_content;
        } else {
            $data['settings_page'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        } else {
            $data['error_warning'] = '';
        }

        $settings = new Settings();
        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $settings->init($this->request->post);
            try {
                $this->model_setting_setting->editSetting('cdek_official', $this->request->post);
                $settings->validate();
                $this->session->data['success'] = $this->language->get('text_success');
            } catch (Exception $exception) {
                $this->session->data['error_warning'] = $this->language->get('error_permission') . $this->language->get($exception->getMessage());
            }
            $this->response->redirect($this->url->link('extension/shipping/cdek_official', 'user_token=' . $this->session->data['user_token']));
        }

        $settings->init($this->model_setting_setting->getSetting('cdek_official'));
        $settings->updateData($data);
        try {
            $settings->validate();
            $cdekApi = new CdekApi($this);
            $status = $cdekApi->checkAuth();
            $data['status_auth'] = $status;
            $cdekTest = new CdekTest($this);
            $cdekTest->test();
        } catch (Exception $exception) {
            $this->session->data['error_warning'] = $this->language->get('error_permission') . $this->language->get($exception->getMessage());
        }

        $data['tariffs'] = $settings->shippingSettings->shippingTariffs;

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/shipping/cdek_official', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/shipping/cdek_official', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('extension/shipping', 'user_token=' . $this->session->data['user_token']);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/cdek_official', $data));
    }

    public function install()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('cdek_official', ['cdek_official_code_status' => 1]);
    }

}