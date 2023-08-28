<?php
class ModelExtensionShippingCdekOfficial extends Model {
    public function getQuote($address) {
        $this->load->language('extension/shipping/cdek_official');

        $status = true;

        $method_data = array();

        if ($status) {
            $quote_data = array();
            $cost = 5.00;

            $quote_data['cdek_official'] = array(
                'code'         => 'cdek_official.cdek_official',
                'title'        => $this->language->get('text_description'),
                'cost'         => $cost,
                'tax_class_id' => 0,
                'text'         => $this->currency->format($cost, $this->session->data['currency'])
            );

            $method_data = array(
                'code'       => 'cdek_official',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('shipping_cdek_official_sort_order'),
                'error'      => false
            );
        }

        return $method_data;
    }
}