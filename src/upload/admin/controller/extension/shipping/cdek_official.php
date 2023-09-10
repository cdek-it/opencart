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
        $app = new App($this, $data, DIR_APPLICATION);
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

        $this->response->setOutput($this->load->view('extension/shipping/cdek_official', $app->data));
    }

    public function install()
    {
        $this->load->model('setting/setting');
        $data['shipping_cdek_official_status'] = 1;
        $this->model_setting_setting->editSetting('shipping_cdek_official', $data);
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $data['shipping_cdek_official_status'] = 0;
        $this->model_setting_setting->editSetting('shipping_cdek_official', $data);
    }
}