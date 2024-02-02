<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\OrderMetaRepository;
use CDEK\RegistrySingleton;

class SaveOrderMetaAction
{
    final public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();

        /** @var \Session $session */
        $session = $registry->get('session');

        if(empty($session->data['cdek_weight'])){
            return;
        }

        try {
            OrderMetaRepository::insertInitialData($session->data['order_id'],
                                               $session->data['cdek_office_code'] ?? '',
                                                   $session->data['cdek_height'],
                                                   $session->data['cdek_width'],
                                                   $session->data['cdek_length'],
                                                   $session->data['cdek_weight']);

        } catch (\Throwable $e) {
        }
    }
}
