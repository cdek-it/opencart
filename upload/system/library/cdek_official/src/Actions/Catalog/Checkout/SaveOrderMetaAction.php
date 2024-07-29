<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\Models\OrderMetaRepository;
use CDEK\RegistrySingleton;
use Request;
use Session;
use Throwable;

class SaveOrderMetaAction
{
    final public function __invoke(?string $officeCode = null): void
    {
        $registry = RegistrySingleton::getInstance();

        $session = $registry->get('session');

        assert($session instanceof Session);

        $request = $registry->get('request');

        assert($request instanceof Request);

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
