<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\RegistrySingleton;
use CDEK\Transport\CdekApi;
use Response;
use Session;
use Throwable;

class GetOfficesAction
{
    public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();

        /** @var Session $session */
        $session = $registry->get('session');

        /** @var Response $response */
        $response = $registry->get('response');

        $response->addHeader('Content-type: application/json');

        if (!isset($session->data['shipping_address']['city'])) {
            $response->addHeader('HTTP/1.1 400 Bad Request');
            $response->setOutput('[]');
        }

        $param = $registry->get('request')->get;

        try {
            $param['city_code'] = CdekApi::getCity($session->data['shipping_address']['city'])[0]['code'];
            $response->setOutput(CdekApi::getOffices($param));
        } catch (Throwable $e) {
            $response->addHeader('HTTP/1.1 500 Internal Server Error');
            $response->setOutput(json_encode(['message' => $e->getMessage()], JSON_THROW_ON_ERROR));
        }
    }
}
