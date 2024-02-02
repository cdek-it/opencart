<?php

namespace CDEK\Actions\Admin\Order;

use CDEK\CdekApi;
use CDEK\OrderMetaRepository;
use CDEK\RegistrySingleton;

class DeleteOrderAction
{
    public function __invoke(int $orderId): void
    {
        $meta = OrderMetaRepository::getOrder($orderId);
        OrderMetaRepository::deleteOrder($orderId);

        CdekApi::deleteOrder($meta['cdek_uuid']);

        RegistrySingleton::getInstance()->get('response')->setOutput((new GetOrderInfoTabAction)($orderId)['content']);
    }
}
