<?php

namespace CDEK\Actions\Admin\Settings;

use CDEK\CdekApi;
use CDEK\RegistrySingleton;
use CDEK\SettingsSingleton;

class GetOfficesAction
{
    public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();
        $registry->get('load')->model('setting/setting');
        $cdekApi               = new CdekApi;
        $param                 = $registry->get('request')->get;
        $param['city_code']    = null;
        $param['is_reception'] = true;
        $registry->get('response')->setOutput($cdekApi->getOffices($param));
    }
}
