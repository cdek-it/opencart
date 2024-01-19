<?php

namespace CDEK\Contracts;

use CDEK\RegistrySingleton;
use Controller;
use Registry;

abstract class ControllerContract extends Controller
{
    public function __construct(Registry $registry) {
        new RegistrySingleton($registry);
        parent::__construct($registry);
    }
}
