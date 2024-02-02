<?php

namespace CDEK\Actions\Admin\Settings;

use CDEK\RegistrySingleton;
use CDEK\Transport\CdekApi;

class GetOfficesAction
{
    /**
     * @throws \JsonException
     */
    public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();
        $param                 = $registry->get('request')->get;
        $param['city_code']    = null;
        $param['is_reception'] = true;
        $registry->get('response')->setOutput(CdekApi::getOffices($param));
    }
}
