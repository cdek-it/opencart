<?php

namespace CDEK\Actions\Admin\Settings;

use CDEK\CdekApi;
use CDEK\RegistrySingleton;
use CDEK\Settings;

class GetOfficesAction
{
    public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();
        $registry->get('load')->model('setting/setting');
        $param    = $registry->get('model_setting_setting')->getSetting('cdek_official');
        $settings = new Settings;
        $settings->init($param);
        $cdekApi               = new CdekApi($settings);
        $param                 = $registry->get('request')->get;
        $param['city_code']    = null;
        $param['is_reception'] = true;
        $registry->get('response')->setOutput($cdekApi->getOffices($param));
    }
}
