<?php

namespace CDEK\Actions\Admin\Order;

use CDEK\OrderMetaRepository;
use CDEK\RegistrySingleton;
use Exception;
use Language;
use Loader;

class GetOrderInfoTabAction
{
    /**
     * @throws Exception
     */
    public function __invoke(int $orderId): array
    {
        $registry = RegistrySingleton::getInstance();
        /** @var Loader $loader */
        $loader = $registry->get('load');

        $loader->model('sale/order');
        $loader->language('extension/shipping/cdek_official');

        /** @var Language $language */
        $language = $registry->get('language');

        $orderInfo = $registry->get('model_sale_order')->getOrder($orderId);

        return [
            'code'    => 'official_cdek',
            'title'   => $language->get('heading_title'),
            'content' => explode('.', $orderInfo['shipping_code'])[0] !== 'cdek_official' ?
                $loader->view('extension/shipping/cdek_official/foreign_delivery') :
                $loader->view('extension/shipping/cdek_official/create_order', [
                    'orderInfo' => $orderInfo,
                    'meta' => OrderMetaRepository::getOrder($orderId),
                ]),
        ];
    }
}
