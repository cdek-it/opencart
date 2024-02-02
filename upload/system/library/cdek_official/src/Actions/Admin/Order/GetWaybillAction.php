<?php

namespace CDEK\Actions\Admin\Order;

use CDEK\CdekApi;
use CDEK\OrderMetaRepository;
use CDEK\RegistrySingleton;

class GetWaybillAction
{
    /**
     * @throws \JsonException
     */
    final public function __invoke(int $orderId): void
    {
        /** @var \Response $response */
        $response = RegistrySingleton::getInstance()->get('response');

        $meta = OrderMetaRepository::getOrder($orderId);
        if (empty($meta['cdek_uuid']) || empty($meta['cdek_number'])) {
            $response->addHeader('HTTP/1.1 404 Not Found');
            $response->setOutput('Waybill not found');
            return;
        }

        $response->setOutput(CdekApi::getWaybill($meta['cdek_uuid']));
        $response->addHeader('Content-Type: application/pdf');
    }
}
