<?php

namespace CDEK\Actions\Admin\Installer;

use CDEK\Helpers\EventsHelper;
use CDEK\Helpers\LogHelper;
use CDEK\RegistrySingleton;

class RemoveExtensionAction
{
    public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();
        LogHelper::write('uninstall start');
        $registry->get('load')->model('setting/setting');
        $data['shipping_cdek_official_status'] = 0;
        $registry->get('model_setting_setting')->editSetting('shipping_cdek_official', $data);
        EventsHelper::deleteEvents();
    }
}
