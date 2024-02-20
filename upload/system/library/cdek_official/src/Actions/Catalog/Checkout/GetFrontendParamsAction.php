<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\Config;
use CDEK\RegistrySingleton;
use CDEK\SettingsSingleton;
use Response;

class GetFrontendParamsAction
{
    final public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();

        /** @var Response $response */
        $response = $registry->get('response');

        /** @var \Session $session */
        $session = $registry->get('session');

        $response->addHeader('Content-Type: application/json');
        $response->addHeader('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        $response->setOutput(json_encode([
                                             'apikey' => SettingsSingleton::getInstance()->authSettings->apiKey,
                                             'city' => $session->data['shipping_address']['city'] ?? null,
                                             'office_code' => $session->data['cdek_office_code'] ?? null,
                                             'office_address' => $session->data['cdek_office_address'] ?? null,
                                             'map_version' => Config::MAP_VERSION,
                                         ], JSON_THROW_ON_ERROR));
    }
}
