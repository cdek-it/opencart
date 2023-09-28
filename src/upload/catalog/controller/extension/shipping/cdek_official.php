<?php

require_once(DIR_SYSTEM . 'library/cdek_official/model/Tariffs.php');
require_once(DIR_SYSTEM . 'library/cdek_official/Service.php');
require_once(DIR_SYSTEM . 'library/cdek_official/CdekOrderMetaRepository.php');

class ControllerExtensionShippingCdekOfficial extends Controller {

    public function index()
    {
        if (isset($this->request->get['cdekRequest'])) {
            $this->load->model('setting/setting');
            $setting = $this->model_setting_setting->getSetting('cdek_official');
            $service = new Service($setting['cdek_official_auth_id'], $setting['cdek_official_auth_secret']);
            $service->process($this->request->get, file_get_contents('php://input'));
        }
    }

    public function cdek_official_checkout_shipping_after(&$route, &$data, &$output)
    {
        $code = [];
        $mapLayout = [];
        if (array_key_exists( 'cdek_official' ,$data['shipping_methods'])) {
            foreach ($data['shipping_methods']['cdek_official']['quote'] as $key => $quote) {
                $separate = explode('_', $key);
                $tariffCode = end($separate);
                $tariffModel = new Tariffs();
                if ($tariffModel->getDirectionByCode((int)$tariffCode) === 'store') {
                    $code[] = $quote['code'];
                    $mapLayout[$quote['code']] = $quote['extra'];
                    unset($data['shipping_methods']['cdek_official']['quote'][$key]['extra']);
                }
            }
        }

        if (!empty($code)) {
            $cdekBlock = '<p><strong>CDEK Official Shipping</strong></p>';
            $pvzCode = '<input type="hidden" id="cdek_official_pvz_code" name="cdek_official_pvz_code" value="">';
            $this->searchAndReplace($output, $cdekBlock, $pvzCode);
            foreach ($code as $quoteCode) {
                $cdekQuoteLayoutMap = $mapLayout[$quoteCode];
                $cdekQuoteBlockPattern = '/<div class="radio">.*?value="' . preg_quote($quoteCode, '/') . '".*?<\/label>/s';

                $output = preg_replace_callback($cdekQuoteBlockPattern, function($matches) use ($cdekQuoteLayoutMap) {
                    return substr($matches[0], 0, -8) . $cdekQuoteLayoutMap . "</label>";
                }, $output);
            }
        }
    }

    public function cdek_official_checkout_checkout_after(&$route, &$data, &$output)
    {
        $header = "<head>";
        $map = DIR_APPLICATION . 'view/theme/default/template/extension/shipping/cdek_official_map_script.twig';
        $script = file_exists($map) ? file_get_contents($map) : '';
        $this->searchAndReplace($output, $header, $script);

        $btnShippingMethod = "data: $('#collapse-shipping-method input[type=\'radio\']:checked, #collapse-shipping-method textarea')";
        $btnShippingMethodWithHide = "data: $('#collapse-shipping-method input[type=\'radio\']:checked, #collapse-shipping-method textarea, #collapse-shipping-method input[type=\'hidden\']')";
        $output = str_replace($btnShippingMethod, $btnShippingMethodWithHide, $output);
    }

    public function cdek_official_checkout_shipping_controller_before()
    {
        $shippingMethod = $this->request->post['shipping_method'];
        $shippingMethodExplode = explode('.', $shippingMethod);
        $shippingMethodName = $shippingMethodExplode[0];
        if ($shippingMethodName === 'cdek_official') {
            $shippingMethodTariff = $shippingMethodExplode[1];
            $shippingMethodTariffExplode = explode('_', $shippingMethodTariff);
            $tariffCode = end($shippingMethodTariffExplode);
            $tariffModel = new Tariffs();
            if ($tariffModel->getDirectionByCode((int)$tariffCode) === 'store') {
                if (isset($this->request->post['cdek_official_pvz_code']) && !empty($this->request->post['cdek_official_pvz_code'])) {
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
            CdekOrderMetaRepository::insertPvzCode($this->db, DB_PREFIX, $this->session->data['order_id'], $cdekPvzCode);
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