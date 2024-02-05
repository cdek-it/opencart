<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\RegistrySingleton;

class SaveOrderCheckoutBeforeAction
{
    final public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();

        /** @var \Session $session */
        $session = $registry->get('session');

        $session->data['cdek_order_id'] = $session->data['order_id'];
    }
}