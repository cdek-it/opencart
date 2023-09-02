<?php
class ControllerExtensionShippingCdekOfficial extends Controller {
    public function index() {
        require_once(DIR_SYSTEM . 'library/cdek_official/Settings.php');
        require_once(DIR_SYSTEM . 'library/cdek_official/Tariffs.php');
        require_once(DIR_SYSTEM . 'library/cdek_official/CdekApi.php');
        require_once(DIR_SYSTEM . 'library/cdek_official/test/CdekTest.php');
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
            $this->model_setting_setting->editSetting('cdek_official', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/shipping/cdek_official', 'user_token=' . $this->session->data['user_token']));
        }
        $cdekApi = new CdekApi($this);
        $status = $cdekApi->checkAuth();

        $cdekTest = new CdekTest($this);
        $cdekTest->test();

        $data['status_auth'] = $status;

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

        $settings = new Settings();
        $data = $settings->init($data, $this->config);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/shipping/cdek_official', $data));
    }

    public function install() {
        $this->load->model('setting/setting');
        $this->model_setting_setting->editSetting('cdek_official', ['cdek_official_code_status' => 1]);
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/shipping/cdek_official')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }
}