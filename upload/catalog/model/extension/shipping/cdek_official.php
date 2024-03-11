<?php

use CDEK\Contracts\ModelContract;
use CDEK\Helpers\DeliveryCalculator;

require_once(DIR_SYSTEM . 'library/cdek_official/vendor/autoload.php');

class ModelExtensionShippingCdekOfficial extends ModelContract
{
    final public function getQuote($address): ?array
    {
        if (empty($address) || !is_array($address)) {
            return null;
        }
        return DeliveryCalculator::getQuoteForAddress($address);
    }
}
