<?php

namespace CDEK\Actions\Admin\Order;

use CDEK\Models\OrderMetaRepository;
use CDEK\RegistrySingleton;
use CDEK\Transport\CdekApi;

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

        $waybill = CdekApi::getWaybill($meta['cdek_uuid']);

        if($waybill === null){
            $response->addHeader('HTTP/1.1 404 Not Found');
            $response->setOutput('Waybill not found');
            return;
        }

        $response->setOutput($waybill);
        $response->addHeader('Content-Type: application/pdf');
    }
}
