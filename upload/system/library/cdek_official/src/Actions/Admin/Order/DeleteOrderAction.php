<?php

namespace CDEK\Actions\Admin\Order;

use CDEK\Models\OrderMetaRepository;
use CDEK\RegistrySingleton;
use CDEK\Transport\CdekApi;

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
