<?php

namespace CDEK\Helpers;

use CDEK\RegistrySingleton;
use CDEK\SettingsSingleton;

class LogHelper
{
    public static function write(string $text): void
    {
        $registry = RegistrySingleton::getInstance();
        $settings = SettingsSingleton::getInstance();

        if($settings->loggerSettings->logMode) {
            $registry->get('log')->write("[CDEK] $text");
        }
    }
}
