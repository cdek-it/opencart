<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\RegistrySingleton;
use Request;
use Session;

class CacheOfficeCodeAction
{
    final public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();

        /** @var Session $session */
        $session = $registry->get('session');
        /** @var Request $request */
        $request = $registry->get('request');

        $session->data['cdek_office_code']    = $request->post['office_code'];
        $session->data['cdek_office_address'] = $request->post['office_address'];

        (new SaveOrderMetaAction)($request->post['office_code']);

        $registry->get('response')->setOutput('Ok');
    }
}
