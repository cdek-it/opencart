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

    public function cdek_official_order_info(&$route, &$data, &$output) {
        $this->log->write('event start');
        $customContent = '<div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-truck"></i> CDEK</h3>
      </div>
      <div class="panel-body">
        <form id="dimensions-form" action="" method="post">
          <div class="form-group">
            <label for="length">Length:</label>
            <input type="number" class="form-control" id="length" name="length" required>
          </div>
          <div class="form-group">
            <label for="width">Width:</label>
            <input type="number" class="form-control" id="width" name="width" required>
          </div>
          <div class="form-group">
            <label for="height">Height:</label>
            <input type="number" class="form-control" id="height" name="height" required>
          </div>
          <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>';

        $search = '<div class="row">';
        $replace = $search . $customContent;

        $output = str_replace($search, $replace, $output);
    }

    public function install()
    {
        $this->load->model('setting/setting');
        $data['shipping_cdek_official_status'] = 1;
        $this->model_setting_setting->editSetting('shipping_cdek_official', $data);
        $this->log->write('install start');
        $this->load->model('extension/shipping/cdek_official');
        $this->model_extension_shipping_cdek_official->createEvents();
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