<?php

use CDEK\Calc;

require_once(DIR_SYSTEM . 'library/cdek_official/vendor/autoload.php');

class ModelExtensionShippingCdekOfficial extends Model
{
    public function getQuote($address)
    {
        $this->load->language('extension/shipping/cdek_official');
        $cartProducts = $this->cart->getProducts();
        $weight       = $this->weight;
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('cdek_official');
        $calc     = new Calc($this->registry, $cartProducts, $settings, $address, $weight);
        return $calc->getMethodData();
    }
}
