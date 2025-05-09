<?php

namespace CDEK\Actions\Admin\Order;

use CDEK\Exceptions\DecodeException;
use CDEK\Exceptions\HttpServerException;
use CDEK\Models\OrderMetaRepository;
use CDEK\RegistrySingleton;
use CDEK\Transport\CdekApi;
use Response;

class GetWaybillAction
{
    final public function __invoke(int $orderId): void
    {
        /** @var Response $response */
        $response = RegistrySingleton::getInstance()->get('response');

        $meta = OrderMetaRepository::getOrder($orderId);
        if (empty($meta['cdek_uuid']) || empty($meta['cdek_number'])) {
            $response->addHeader('HTTP/1.1 404 Not Found');
            $response->setOutput('Waybill not found');
            return;
        }

        try {
            $waybill = CdekApi::getWaybill($meta['cdek_uuid']);
        } catch ( DecodeException | HttpServerException  $e) {
            $response->addHeader('HTTP/1.1 503 Service Unavailable');
            $response->setOutput('External Server Error');
            return;
        }

        if($waybill === null){
            $response->addHeader('HTTP/1.1 404 Not Found');
            $response->setOutput('Waybill not found');
            return;
        }

        $response->setOutput($waybill);
        $response->addHeader('Content-Type: application/pdf');
    }
}
