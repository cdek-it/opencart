<?php
class ModelExtensionShippingCdekOfficial extends Model {
    public function getQuote($address) {
        $this->load->model('setting/setting');
        $settings = $this->model_setting_setting->getSetting('cdek_official');

        if (isset($settings['cdek_official_status']) && $settings['cdek_official_status'] == 1) {

            $quote_data = array();

            $quote_data['cdek_official'] = array(
                'code'         => 'cdek_official.cdek_official',
                'title'        => 'CDEK Official Shipping',
                'cost'         => 6.00,
                'tax_class_id' => 0,
                'text'         => '$6.00'
            );

            // Define the method data
            $method_data = array(
                'code'       => 'cdek_official',
                'title'      => 'CDEK Official Shipping',
                'quote'      => $quote_data,
                'sort_order' => 1,
                'error'      => false
            );

            return $method_data;
        } else {
            return;
        }
    }
}