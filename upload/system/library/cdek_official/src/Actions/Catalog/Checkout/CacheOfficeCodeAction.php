<?php

namespace CDEK\Actions\Catalog\Checkout;

use CDEK\RegistrySingleton;
use Request;
use Response;
use Session;

class CacheOfficeCodeAction
{
    final public function __invoke(): void
    {
        $registry = RegistrySingleton::getInstance();

        $session = $registry->get('session');

        assert($session instanceof Session);

        $request = $registry->get('request');

        assert($request instanceof Request);

        $session->data['cdek_office_code']    = $request->post['office_code'];
        $session->data['cdek_office_address'] = $request->post['office_address'];

        (new SaveOrderMetaAction)($request->post['office_code']);

        $response = $registry->get('response');

        assert($response instanceof Response);

        $response->setOutput('Ok');
    }
}
