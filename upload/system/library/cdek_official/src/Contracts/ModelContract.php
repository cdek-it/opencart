<?php

namespace CDEK\Contracts;

use CDEK\RegistrySingleton;

abstract class ModelContract extends \Model
{
    public function __construct($registry) {
        new RegistrySingleton($registry);
        parent::__construct($registry);
    }
}
