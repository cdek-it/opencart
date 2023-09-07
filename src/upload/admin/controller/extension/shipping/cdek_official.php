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
        $app->run();

        $app->data['action'] = $this->url->link('extension/shipping/cdek_official', 'user_token=' . $this->session->data['user_token']);
        $app->data['cancel'] = $this->url->link('extension/shipping', 'user_token=' . $this->session->data['user_token']);
        $app->data['header'] = $this->load->controller('common/header');
        $app->data['column_left'] = $this->load->controller('common/column_left');
        $app->data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/cdek_official', $app->data));
    }
}