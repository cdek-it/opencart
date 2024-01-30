<?php

namespace CDEK\Actions\Admin\Installer;

use CDEK\OrderMetaRepository;
use CDEK\Helpers\EventsHelper;
use CDEK\Helpers\LogHelper;
use CDEK\RegistrySingleton;

class InstallExtensionAction
{
    public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();
        $registry->get('load')->model('setting/setting');
        $data['shipping_cdek_official_status'] = 1;
        $registry->get('model_setting_setting')->editSetting('shipping_cdek_official', $data);
        LogHelper::write('install start');

        EventsHelper::registerEvents();
        OrderMetaRepository::create();
    }
}
