<?php
class ControllerExtensionShippingCdekOfficial extends Controller {

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
                $cdekQuoteBlockPattern = '/<div class="radio">.*?value="' . preg_quote($quoteCode, '/') . '".*?<\/div>/s';

                $output = preg_replace_callback($cdekQuoteBlockPattern, function($matches) use ($cdekQuoteLayoutMap) {
                    return $matches[0] . $cdekQuoteLayoutMap;
                }, $output);
            }
        }
    }

    public function cdek_official_checkout_shipping_method_before(&$route, &$data, &$output)
    {

        $cdekBlock = '{{ quote.title }} - {{ quote.text }}</label>';
        $pvzCode = '{{ quote.extra|raw }}';
        $this->searchAndReplace($output, $cdekBlock, $pvzCode);

    }

    public function cdek_official_checkout_checkout_after(&$route, &$data, &$output)
    {
        $header = "<head>";
        $map = DIR_APPLICATION . 'view/theme/default/template/extension/shipping/cdek_official_map_script.twig';
        $script = file_exists($map) ? file_get_contents($map) : '';
        $this->searchAndReplace($output, $header, $script);
    }

    public function cdek_official_header_before(&$route, &$data, &$output)
    {
//        $data['scripts'][]='https://cdn.jsdelivr.net/gh/cdek-it/widget@latest/dist/cdek-widget.umd.js';
    }

    private function searchAndReplace(&$output, $search, $replace)
    {
        $pos = strpos($output, $search);

        if ($pos !== false) {
            $insertPos = $pos + strlen($search);
            $output = substr_replace($output, $replace, $insertPos, 0);
        }

    }

//if (isset($this->request->post['cdek_official_pvz_code']) && empty($this->request->post['cdek_official_pvz_code'])) {
//$json['error']['warning'] = "Pvz is required";
//$this->response->setOutput(json_encode($json));
//return;
//}

//shipping_method 112
//if (isset($this->request->post['cdek_number_customer']) && empty($this->request->post['cdek_number_customer'])) {
//$json['error']['warning'] = "Phone is required";
//$this->response->setOutput(json_encode($json));
//return;
//}
//$this->session->data['cdek_official_pvz_code'] = $this->request->post['cdek_official_pvz_code'];

//129
//if (isset($this->request->post['cdek_number_customer'])) {
//$this->session->data['cdek_number_customer'] = strip_tags($this->request->post['cdek_number_customer']);
//}
}