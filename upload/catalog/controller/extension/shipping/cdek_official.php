<?php

use CDEK\CdekApi;
use CDEK\CdekConfig;
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
        $this->load->language('extension/shipping/cdek_official');
        $param    = $this->model_setting_setting->getSetting('cdek_official');
        $settings = new Settings;
        $settings->init($param);
        $cdekApi = new CdekApi($this->registry, $settings);

        $param = $this->request->get;
        if (isset($this->request->get['cdekRequest']) && $this->request->get['cdekRequest'] === 'adminMap') {
            $param['city_code']    = null;
            $param['is_reception'] = true;
            $this->response->setOutput($cdekApi->getOffices($param));
        } else {
            try {
                $city               = $cdekApi->getCity($this->session->data['shipping_address']['city']);
                $param['city_code'] = $city[0]->code;
                $offices            = $cdekApi->getOffices($param);
                if (empty($offices)) {
                    throw new Exception($this->language->get('cdek_shipping__office_not_found'));
                }
                $this->response->setOutput($cdekApi->getOffices($param));
            } catch (Exception $e) {
                $this->response->addHeader('HTTP/1.1 500 Internal Server Error');
                $this->response->setOutput(json_encode(array('message' => $e->getMessage())));
            }
        }
    }

    public function cdek_official_checkout_checkout_after(&$route, &$data, &$output)
    {
        //for cdek_official_pvz_code add session
        $postParamForTransfer
                = "data: $('#collapse-shipping-method input[type=\'radio\']:checked, #collapse-shipping-method textarea')";
        $postParamForTransferEdited
                = "data: $('#collapse-shipping-method input[type=\'radio\']:checked, #collapse-shipping-method textarea, #collapse-shipping-method input[type=\'hidden\']')";
        $output = str_replace($postParamForTransfer, $postParamForTransferEdited, $output);
    }

    public function cdek_official_checkout_shipping_controller_after(&$route, &$data, &$output)
    {
        $this->session->data['shipping_method']['title'] = $this->session->data['shipping_method']['extra'];
        $shippingMethod                                  = $this->request->post['shipping_method'];
        $shippingMethodExplode                           = explode('.', $shippingMethod);
        $shippingMethodName                              = $shippingMethodExplode[0];
        if ($shippingMethodName === 'cdek_official') {
            $shippingMethodTariff        = $shippingMethodExplode[1];
            $shippingMethodTariffExplode = explode('_', $shippingMethodTariff);
            $tariffCode                  = end($shippingMethodTariffExplode);
            $tariffModel                 = new Tariffs;
            if ($tariffModel->getDirectionByCode((int)$tariffCode) === 'store' ||
                $tariffModel->getDirectionByCode((int)$tariffCode) === 'postamat') {
                if (!empty($this->request->post['cdek_official_pvz_code'])) {
                    $this->load->model('setting/setting');
                    $param    = $this->model_setting_setting->getSetting('cdek_official');
                    $settings = new Settings;
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
            try {
                CdekOrderMetaRepository::insertPvzCode($this->db,
                                                       DB_PREFIX,
                                                       $this->session->data['order_id'],
                                                       $this->session->data['cdek_official_pvz_code']);

            } catch (Exception $e) {
            }
            unset($this->session->data['cdek_official_pvz_code']);
        }
    }

    public function addCheckoutHeaderScript(&$route, &$data)
    {
        $data['scripts'][] = 'catalog/view/javascript/shipping/cdek_official.js';
        $data['scripts'][] = 'https://cdn.jsdelivr.net/gh/cdek-it/widget@' .
                             CdekConfig::MAP_VERSION .
                             '/dist/cdek-widget.umd.js';
    }

    private function searchAndReplace(&$output, $search, $replace)
    {
        $pos = strpos($output, $search);

        if ($pos !== false) {
            $insertPos = $pos + strlen($search);
            $output    = substr_replace($output, $replace, $insertPos, 0);
        }
    }
}
