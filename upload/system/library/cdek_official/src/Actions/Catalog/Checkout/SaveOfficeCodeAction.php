<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\OrderMetaRepository;
use CDEK\RegistrySingleton;

class SaveOfficeCodeAction
{
    final public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();

        /** @var \Session $session */
        $session = $registry->get('session');
        $office = $session->data['cdek_office_code'];

        if(empty($office)){
            return;
        }

        try {
            OrderMetaRepository::insertOfficeCode($session->data['order_id'],
                                               $session->data['cdek_office_code']);

        } catch (\Throwable $e) {
        }
    }
}
