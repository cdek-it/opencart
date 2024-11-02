<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\Models\OrderMetaRepository;
use CDEK\RegistrySingleton;
use Session;
use Throwable;

class SaveOrderMetaAction
{
    final public function __invoke(?string $officeCode = null): void
    {
        $registry = RegistrySingleton::getInstance();

        /** @var Session $session */
        $session = $registry->get('session');

        if (empty($session->data['cdek_weight']) || empty($session->data['order_id'])) {
            return;
        }

        try {
            OrderMetaRepository::insertInitialData(
                $session->data['order_id'],
                $session->data['cdek_height'],
                $session->data['cdek_width'],
                $session->data['cdek_length'],
                $session->data['cdek_weight'],
            );

            if ($officeCode !== null || !empty($session->data['cdek_office_code'])) {
                OrderMetaRepository::insertOfficeCode(
                    $session->data['order_id'],
                    $officeCode ?? $session->data['cdek_office_code'],
                );
            }
        } catch (Throwable $e) {
        }
    }
}
