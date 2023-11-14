<?php

namespace CDEK;

class CdekHelper
{
    static function getLocality($locality) {
        $shippingOfficeJson = html_entity_decode($locality);
        return json_decode($shippingOfficeJson);
    }
}