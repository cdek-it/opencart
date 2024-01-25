<?php

namespace CDEK\Actions\Admin\Order;

use CDEK\RegistrySingleton;

class GetOrderInfoTabAction
{
    /**
     * @throws \Exception
     */
    public function __invoke(int $orderId): array
    {
        $registry = RegistrySingleton::getInstance();
        /** @var \Loader $loader */
        $loader = $registry->get('load');

        $loader->model('sale/order');
        $loader->language('extension/shipping/cdek_official');

        /** @var \Language $language */
        $language = $registry->get('language');

        $orderInfo = $registry->get('model_sale_order')->getOrder($orderId);

        if(explode('.', $orderInfo['shipping_code'])[0] !== 'cdek_official') {
            return [];
        }

        return [
            'code' => 'official_cdek',
            'title' => $language->get('heading_title'),
            'content' => '<div>123</div>',
        ];
    }
}
