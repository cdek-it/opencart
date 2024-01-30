<?php

namespace CDEK\Models;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;
use libphonenumber\PhoneNumberUtil;
use RuntimeException;

class SettingsSeller extends ValidatableSettingsContract
{
    public string $sellerInternationalShippingCheckbox = 'no';
    public string $shippingSellerName = '';
    public string $shippingSellerPhone = '';
    public string $sellerTrueSellerAddress = '';
    public string $sellerShipper = '';
    public string $sellerShipperAddress = '';
    public string $sellerPassportSeries = '';
    public string $sellerPassportNumber = '';
    public string $sellerPassportIssueDate = '';
    public string $sellerPassportIssuingAuthority = '';
    public string $sellerTin = '';
    public string $sellerDateOfBirth = '';

    /**
     * @throws Exception
     */
    public function validate(): void
    {
        if (empty($this->shippingSellerName)) {
            throw new RuntimeException('cdek_error_shipping_seller_name_empty');
        }

        if (strlen($this->shippingSellerName) > 255) {
            throw new RuntimeException('cdek_error_shipping_seller_name_too_long');
        }

        if (empty($this->shippingSellerPhone)) {
            throw new RuntimeException('cdek_error_shipping_seller_phone_empty');
        }

        $phoneNumUtil = PhoneNumberUtil::getInstance();

        if (!$phoneNumUtil->isValidNumber($phoneNumUtil->parse($this->shippingSellerPhone))) {
            throw new RuntimeException('cdek_error_shipping_seller_phone_invalid_format');
        }

        if ($this->sellerInternationalShippingCheckbox !== '1') {
            return;
        }

        if (empty($this->sellerTrueSellerAddress)) {
            throw new RuntimeException('cdek_error_seller_true_seller_address_empty');
        }

        if (strlen($this->sellerTrueSellerAddress) > 255) {
            throw new RuntimeException('cdek_error_seller_true_seller_address_length');
        }

        if (empty($this->sellerShipper)) {
            throw new RuntimeException('cdek_error_seller_shipper_empty');
        }

        if (empty($this->sellerShipperAddress)) {
            throw new RuntimeException('cdek_error_seller_shipper_address_empty');
        }

        if (empty($this->sellerPassportSeries)) {
            throw new RuntimeException('cdek_error_seller_passport_series_empty');
        }

        if (empty($this->sellerPassportNumber)) {
            throw new RuntimeException('cdek_error_seller_passport_number_empty');
        }

        if (empty($this->sellerPassportIssueDate)) {
            throw new RuntimeException('cdek_error_seller_passport_issue_date_empty');
        }

        if (empty($this->sellerPassportIssuingAuthority)) {
            throw new RuntimeException('cdek_error_seller_passport_issuing_authority_empty');
        }

        if (empty($this->sellerTin)) {
            throw new RuntimeException('cdek_error_seller_tin_empty');
        }

        if (empty($this->sellerDateOfBirth)) {
            throw new RuntimeException('cdek_error_seller_date_of_birth_empty');
        }
    }
}
