<?php

use CDEK\Contracts\ModelContract;
use CDEK\DeliveryCalculator;

require_once(DIR_SYSTEM . 'library/cdek_official/vendor/autoload.php');

class ModelExtensionShippingCdekOfficial extends ModelContract
{
    final public function getQuote(array $address): ?array
    {
        return DeliveryCalculator::getQuoteForAddress($address);
    }
}
