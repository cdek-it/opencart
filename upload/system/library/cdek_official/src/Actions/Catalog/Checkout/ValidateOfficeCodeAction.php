<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\RegistrySingleton;
use JsonException;

class ValidateOfficeCodeAction
{
    /**
     * @throws JsonException
     */
    final public function __invoke(): ?bool
    {
        $registry = RegistrySingleton::getInstance();
        $shippingMethodCode = $registry->get('request')->post['shipping_method'] ?? null;

        if (!is_string($shippingMethodCode) || $shippingMethodCode === '') {
            return null;
        }

        $shippingMethod = explode('.', $shippingMethodCode, 2);

        if (count($shippingMethod) !== 2) {
            return null;
        }

        if (($shippingMethod[0] !== 'cdek_official') ||
            !empty($registry->get('session')->data['cdek_office_code']) ||
            explode('_', $shippingMethod[1], 2)[0] === 'door') {
            return null;
        }

        $response = $registry->get('response');
        $response->addHeader('Content-Type: application/json');

        $registry->get('load')->language('extension/shipping/cdek_official');

        $response->setOutput(json_encode([
                                             'error' => [
                                                 'warning' => $registry->get('language')->get('cdek_pvz_not_found'),
                                             ],
                                         ], JSON_THROW_ON_ERROR));
        return false;
    }
}
