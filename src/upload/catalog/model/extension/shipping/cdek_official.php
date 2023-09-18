<?php
require_once(DIR_SYSTEM . 'library/cdek_official/Calc.php');
class ModelExtensionShippingCdekOfficial extends Model {
    public function getQuote($address) {
        $this->load->language('extension/shipping/cdek_official');
        $cartProducts = $this->cart->getProducts();
        $weight = $this->cart->weight;
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('cdek_official');
        $calc = new Calc($this->registry, $cartProducts, $settings, $address['postcode'], $weight);
        return $calc->getMethodData();
    }
}