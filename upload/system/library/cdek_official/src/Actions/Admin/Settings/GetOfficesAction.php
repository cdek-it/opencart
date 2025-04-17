<?php

namespace CDEK\Actions\Admin\Settings;

use CDEK\RegistrySingleton;
use CDEK\Transport\CdekApi;
use Throwable;

class GetOfficesAction
{
    public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();
        $param                 = $registry->get('request')->get;
        $param['city_code']    = null;
        $param['is_reception'] = true;
        $response = $registry->get('response');

        try {
            $response->setOutput(CdekApi::getOffices($param));
        } catch (Throwable $e) {
            $response->addHeader('HTTP/1.1 503 Service Unavailable');
            $response->setOutput('[]');
        }

    }
}
