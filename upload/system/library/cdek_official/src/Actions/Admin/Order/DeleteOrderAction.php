<?php

namespace CDEK\Actions\Admin\Order;

use CDEK\Exceptions\DecodeException;
use CDEK\Exceptions\HttpServerException;
use CDEK\Models\OrderMetaRepository;
use CDEK\RegistrySingleton;
use CDEK\Transport\CdekApi;
use Exception;

class DeleteOrderAction
{
    /**
     * @throws Exception
     */
    public function __invoke(int $orderId): void
    {
        $meta = OrderMetaRepository::getOrder($orderId);
        OrderMetaRepository::deleteOrder($orderId);

        try {
            CdekApi::deleteOrder($meta['cdek_uuid']);
        } catch ( DecodeException | HttpServerException $e) {
            $registry = RegistrySingleton::getInstance();

            $registry->get('session')->data['errors'][] = $e->getMessage();
        }

        RegistrySingleton::getInstance()->get('response')->setOutput((new GetOrderInfoTabAction)($orderId)['content']);
    }
}
