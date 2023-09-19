<?php
class ControllerExtensionShippingCdekOfficial extends Controller {

    public function index()
    {
        $data['header'] = $this->load->controller('common/header');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
    }
    public function cdek_official_checkout_shipping_after(&$route, &$data, &$output)
    {
//        $cdekBlock = '<p><strong>CDEK Official Shipping</strong></p>';
//
//        $map = DIR_APPLICATION . 'view/theme/default/template/extension/shipping/cdek_official_map.twig';
//        $mapLayout = file_exists($map) ? file_get_contents($map) : '';
//        $this->searchAndReplace($output, $cdekBlock, $mapLayout);

    }

    public function cdek_official_checkout_checkout_before(&$route, &$data, &$output)
    {
//        $this->document->addScript('https://cdn.jsdelivr.net/gh/cdek-it/widget@latest/dist/cdek-widget.umd.js');
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

//shipping_method 112
//if (isset($this->request->post['cdek_number_customer']) && empty($this->request->post['cdek_number_customer'])) {
//$json['error']['warning'] = "Phone is required";
//$this->response->setOutput(json_encode($json));
//return;
//}


//129
//if (isset($this->request->post['cdek_number_customer'])) {
//$this->session->data['cdek_number_customer'] = strip_tags($this->request->post['cdek_number_customer']);
//}
}