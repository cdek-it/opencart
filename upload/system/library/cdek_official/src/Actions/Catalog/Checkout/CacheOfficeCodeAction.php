<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\RegistrySingleton;

class CacheOfficeCodeAction
{
    final public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();

        $registry->get('session')->data['cdek_office_code'] = $registry->get('request')->post['office_code'];
        $registry->get('session')->data['cdek_office_address'] = $registry->get('request')->post['office_address'];

        /** @var \Response $response */
        $response = $registry->get('response');

        $response->setOutput('Ok');
    }
}
