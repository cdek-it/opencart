<?php

class ModelExtensionShippingCdekOfficial extends Model
{

    private const EVENTS = [
        'admin/view/sale/order_info/after' => [
            'cdek_official_order' => 'extension/shipping/cdek_official/cdek_official_order_info',
        ],
        'catalog/view/checkout/checkout/after' => [
            'cdek_official_checkout' => 'extension/shipping/cdek_official/cdek_official_checkout_checkout_after',
        ],
        'catalog/controller/checkout/shipping_method/save/after' => [
            'cdek_official_controller' => 'extension/shipping/cdek_official/cdek_official_checkout_shipping_controller_before',
        ],
        'catalog/controller/checkout/confirm/after' => [
            'cdek_official_checkout_confirm' => 'extension/shipping/cdek_official/cdek_official_checkout_confirm_after',
        ],
    ];

    private const OBSOLETE_EVENTS = [
        'cdek_official_shipping',
        'cdek_official_checkout_map',
    ];

    public function createEvents()
    {
        $this->log->write('create events');
        $this->load->model('setting/event');

        foreach (self::EVENTS as $trigger => $actions) {
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

        foreach (self::EVENTS as $actions) {
            foreach (array_keys($actions) as $event) {
                $this->model_setting_event->deleteEventByCode($event);
            }
        }

        foreach (self::OBSOLETE_EVENTS as $event) {
            $this->model_setting_event->deleteEventByCode($event);
        }
    }
}
