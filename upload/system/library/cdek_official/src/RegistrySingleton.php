<?php

namespace CDEK;

use Registry;
use RuntimeException;

class RegistrySingleton
{
    private static Registry $registry;

    public function __construct(Registry $registry){
        self::$registry = $registry;
    }

    public static function getInstance(): Registry{
        if(empty(self::$registry)){
            throw new RuntimeException('Calling singleton instance before initialization');
        }

        return self::$registry;
    }
}
