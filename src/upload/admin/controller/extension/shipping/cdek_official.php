<?php
class ControllerExtensionShippingCdekOfficial extends Controller {
    public function index() {
        $this->load->language('extension/shipping/cdek_official');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
            $this->model_setting_setting->editSetting('shipping_cdek_official', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/shipping/cdek_official', 'user_token=' . $this->session->data['user_token']));
        }

        $data['action'] = $this->url->link('extension/shipping/cdek_official', 'user_token=' . $this->session->data['user_token']);
        $data['cancel'] = $this->url->link('extension/shipping', 'user_token=' . $this->session->data['user_token']);

        if (isset($this->request->post['shipping_cdek_official_status'])) {
            $data['shipping_cdek_official_status'] = $this->request->post['shipping_cdek_official_status'];
        } else {
            $data['shipping_cdek_official_status'] = $this->config->get('shipping_cdek_official_status');
        }

        if (isset($this->request->post['shipping_cdek_official_cost'])) {
            $data['shipping_cdek_official_cost'] = $this->request->post['shipping_cdek_official_cost'];
        } else {
            $data['shipping_cdek_official_cost'] = $this->config->get('shipping_cdek_official_cost');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/cdek_official', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/shipping/cdek_official')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}