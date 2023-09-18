<?php
class ModelExtensionShippingCdekOfficial extends Model {

    private $events = array(
        'admin/view/sale/order_info/after' => array(
            'extension/shipping/cdek_official/cdek_official_order_info'
        ),
        'catalog/view/checkout/shipping_method/after' => [
            'extension/shipping/cdek_official/cdek_official_checkout_shipping_after'
        ]
    );

//    public function getQuote($address) {
//        $this->load->model('setting/setting');
//        $settings = $this->model_setting_setting->getSetting('cdek_official');
//
//        if (isset($settings['cdek_official_status']) && $settings['cdek_official_status'] == 1) {
//
//            $quote_data = array();
//
//            $quote_data['cdek_official'] = array(
//                'code'         => 'cdek_official.cdek_official',
//                'title'        => 'CDEK Official Shipping',
//                'cost'         => 6.00,
//                'tax_class_id' => 0,
//                'text'         => '$6.00'
//            );
//
//            // Define the method data
//            $method_data = array(
//                'code'       => 'cdek_official',
//                'title'      => 'CDEK Official Shipping',
//                'quote'      => [],
//                'sort_order' => 1,
//                'error'      => false
//            );
//
//            return $method_data;
//        } else {
//            return;
//        }
//    }

    public function createEvents() {
        $this->log->write('create events');
        $this->load->model('setting/event');

        foreach ($this->events as $trigger => $actions) {
            foreach ($actions as $action) {
                $this->model_setting_event->addEvent('shipping_cdek_official', $trigger, $action, 1, 0);
            }
        }
    }

    public function deleteEvents() {
        $this->load->model('setting/event');

        $this->model_setting_event->deleteEventByCode('shipping_cdek_official');
    }
}