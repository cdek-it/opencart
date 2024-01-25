<?php

namespace CDEK\Helpers;

use CDEK\RegistrySingleton;

class EventsHelper
{
    private const EVENTS
        = [
            'admin/view/sale/order_info/before'                       => [
                'cdek_official_order' => 'extension/shipping/cdek_official/orderInfo',
            ],
            'catalog/view/checkout/checkout/after'                   => [
                'cdek_official_checkout' => 'extension/shipping/cdek_official/cdek_official_checkout_checkout_after',
            ],
            'catalog/controller/checkout/shipping_method/save/after' => [
                'cdek_official_controller' => 'extension/shipping/cdek_official/cdek_official_checkout_shipping_controller_after',
            ],
            'catalog/controller/checkout/confirm/after'              => [
                'cdek_official_checkout_confirm' => 'extension/shipping/cdek_official/cdek_official_checkout_confirm_after',
            ],
            'catalog/view/common/header/before'                      => [
                'cdek_official_header_before' => 'extension/shipping/cdek_official/addCheckoutHeaderScript',
            ],
        ];

    private const OBSOLETE_EVENTS
        = [
            'cdek_official_shipping',
            'cdek_official_checkout_map',
        ];

    public static function registerEvents(): void
    {
        $registry = RegistrySingleton::getInstance();

        LogHelper::write('create events');
        $registry->get('load')->model('setting/event');
        /** @var \ModelSettingEvent $eventModel */
        $eventModel = $registry->get('model_setting_event');

        foreach (self::EVENTS as $trigger => $actions) {
            foreach ($actions as $actionName => $action) {
                if (empty($eventModel->getEventByCode($actionName))) {
                    $eventModel->addEvent($actionName, $trigger, $action, 1, 0);
                }
            }
        }
    }

    public static function deleteEvents(): void
    {
        $registry = RegistrySingleton::getInstance();

        LogHelper::write('delete events');
        $registry->get('load')->model('setting/event');
        /** @var \ModelSettingEvent $eventModel */
        $eventModel = $registry->get('model_setting_event');

        foreach (self::EVENTS as $actions) {
            foreach (array_keys($actions) as $event) {
                $eventModel->deleteEventByCode($event);
            }
        }

        foreach (self::OBSOLETE_EVENTS as $event) {
            $eventModel->deleteEventByCode($event);
        }
    }
}
