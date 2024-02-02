<?php

namespace CDEK\Helpers;

use CDEK\RegistrySingleton;

class LogHelper
{
    public static function write(string $text): void
    {
        $registry = RegistrySingleton::getInstance();

        $registry->get('log')->write("[CDEK] $text");
    }
}
