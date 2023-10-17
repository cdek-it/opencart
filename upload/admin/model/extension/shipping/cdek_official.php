<?php

class ModelExtensionShippingCdekOfficial extends Model
{

    private $events = [
        'admin/view/sale/order_info/after' => [
            'cdek_official_order' => 'extension/shipping/cdek_official/cdek_official_order_info',
        ],
        'catalog/view/checkout/checkout/after' => [
            'cdek_official_checkout' => 'extension/shipping/cdek_official/cdek_official_checkout_checkout_after',
        ],
        'catalog/view/checkout/shipping_method/after' => [
            'cdek_official_shipping' => 'extension/shipping/cdek_official/cdek_official_checkout_shipping_after',
        ],
        'catalog/controller/checkout/shipping_method/save/after' => [
            'cdek_official_controller' => 'extension/shipping/cdek_official/cdek_official_checkout_shipping_controller_before',
        ],
        'catalog/controller/checkout/confirm/after' => [
            'cdek_official_checkout_confirm' => 'extension/shipping/cdek_official/cdek_official_checkout_confirm_after',
        ],
    ];

    public function createEvents()
    {
        $this->log->write('create events');
        $this->load->model('setting/event');

        foreach ($this->events as $trigger => $actions) {
            foreach ($actions as $actionName => $action) {
                if (empty($this->model_setting_event->getEventByCode($actionName))) {
                    $this->model_setting_event->addEvent($actionName, $trigger, $action, 1, 0);
                }
            }
        }
    }

    public function deleteEvents()
    {
        $this->load->model('setting/event');

        foreach ($this->events as $actions) {
            foreach (array_keys($actions) as $event) {
                $this->model_setting_event->deleteEventByCode($event);
            }
        }
    }
}
