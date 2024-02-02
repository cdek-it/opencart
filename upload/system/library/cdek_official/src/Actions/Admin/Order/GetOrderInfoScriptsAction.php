<?php

namespace CDEK\Actions\Admin\Order;

use CDEK\RegistrySingleton;

class GetOrderInfoScriptsAction
{
    final public function __invoke(): void
    {
        /** @var \Document $document */
        $document = RegistrySingleton::getInstance()->get('document');

        $document->addScript('view/javascript/cdek_official/create_order.js');
        $document->addStyle('view/stylesheet/cdek_official/create_order.css');
    }
}
