<?php
class ModelExtensionShippingCdekOfficial extends Model {

    private $events = array(
        'admin/view/sale/order_info/after' => array(
            'extension/shipping/cdek_official/cdek_official_order_info'
        ),
        'catalog/view/checkout/checkout/after' => [
            'extension/shipping/cdek_official/cdek_official_checkout_checkout_after'
        ],
        'catalog/view/checkout/shipping_method/after' => [
            'extension/shipping/cdek_official/cdek_official_checkout_shipping_after'
        ],
        'catalog/controller/checkout/shipping_method/save/after' => [
            'extension/shipping/cdek_official/cdek_official_checkout_shipping_controller_before'
        ],
        'catalog/controller/checkout/confirm/after' => [
            'extension/shipping/cdek_official/cdek_official_checkout_confirm_after'
        ],
    );

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