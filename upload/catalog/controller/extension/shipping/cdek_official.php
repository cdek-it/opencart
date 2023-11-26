<?php

use CDEK\CdekApi;
use CDEK\CdekOrderMetaRepository;
use CDEK\model\Tariffs;
use CDEK\Service;
use CDEK\Settings;

require_once(DIR_SYSTEM . 'library/cdek_official/vendor/autoload.php');

class ControllerExtensionShippingCdekOfficial extends Controller
{

    public function index(): void
    {
        $this->load->model('setting/setting');
        $param = $this->model_setting_setting->getSetting('cdek_official');
        $settings = new Settings();
        $settings->init($param);
        $cdekApi = new CdekApi($this->registry, $settings);

        $param = $this->request->get;
        if (isset($this->request->get['cdekRequest']) && $this->request->get['cdekRequest'] === 'adminMap') {
            $param['city_code'] = null;
            $param['is_reception'] = true;
            $this->response->setOutput($cdekApi->getOffices($param));
        } else {
            $city = $cdekApi->getCity($this->session->data['shipping_address']['city']);
            $param['city_code'] = $city[0]->code;
            $this->response->setOutput($cdekApi->getOffices($param));
        }
    }

    public function cdek_official_checkout_checkout_before(&$route, &$data, &$output)
    {
        $data['scripts'][] = 'catalog/view/javascript/cdek_official/cdek_official_checkout.js';
//        $this->document->addScript('catalog/view/javascript/cdek_official/cdek_official_checkout.js');
    }

    public function cdek_official_checkout_checkout_after(&$route, &$data, &$output)
    {
        //for cdek_official_pvz_code
        $postParamForTransfer =
            "data: $('#collapse-shipping-method input[type=\'radio\']:checked, #collapse-shipping-method textarea')";
        $postParamForTransferEdited =
            "data: $('#collapse-shipping-method input[type=\'radio\']:checked, #collapse-shipping-method textarea, #collapse-shipping-method input[type=\'hidden\']')";
        $output = str_replace($postParamForTransfer, $postParamForTransferEdited, $output);
    }

    public function cdek_official_checkout_shipping_controller_before(&$route, &$data, &$output)
    {
        $shippingMethod = $this->request->post['shipping_method'];
        $shippingMethodExplode = explode('.', $shippingMethod);
        $shippingMethodName = $shippingMethodExplode[0];
        if ($shippingMethodName === 'cdek_official') {
            $shippingMethodTariff = $shippingMethodExplode[1];
            $shippingMethodTariffExplode = explode('_', $shippingMethodTariff);
            $tariffCode = end($shippingMethodTariffExplode);
            $tariffModel = new Tariffs();
            if ($tariffModel->getDirectionByCode((int)$tariffCode) === 'store' ||
                $tariffModel->getDirectionByCode((int)$tariffCode) === 'postamat') {
                if (isset($this->request->post['cdek_official_pvz_code']) &&
                    !empty($this->request->post['cdek_official_pvz_code'])) {
                    $this->load->model('setting/setting');
                    $param = $this->model_setting_setting->getSetting('cdek_official');
                    $settings = new Settings();
                    $settings->init($param);
                    $this->session->data['cdek_official_pvz_code'] = $this->request->post['cdek_official_pvz_code'];
                } else {
                    $this->load->language('extension/shipping/cdek_official');
                    $json['error']['warning'] = $this->language->get('cdek_pvz_not_found');
                    $this->response->addHeader('Content-Type: application/json');
                    $this->response->setOutput(json_encode($json));
                }
            }
        }
    }

    public function cdek_official_checkout_confirm_after()
    {
        if (isset($this->session->data['order_id']) && isset($this->session->data['cdek_official_pvz_code'])) {
            $cdekPvzCode = $this->session->data['cdek_official_pvz_code'];
            CdekOrderMetaRepository::insertPvzCode($this->db,
                                                   DB_PREFIX,
                                                   $this->session->data['order_id'],
                                                   $cdekPvzCode);
            unset($this->session->data['cdek_official_pvz_code']);
        }
    }

    private function searchAndReplace(&$output, $search, $replace)
    {
        $pos = strpos($output, $search);

        if ($pos !== false) {
            $insertPos = $pos + strlen($search);
            $output = substr_replace($output, $replace, $insertPos, 0);
        }

    }
}
