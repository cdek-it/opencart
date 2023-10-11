<?php

namespace CDEK\model;

    use Exception;
    use libphonenumber\PhoneNumber;
    use libphonenumber\PhoneNumberUtil;

    class SettingsSeller extends AbstractSettings
    {
        public $sellerInternationalShippingCheckbox;
        public $shippingSellerName;
        public $shippingSellerPhone;
        public $sellerTrueSellerAddress;
        public $sellerShipper;
        public $sellerShipperAddress;
        public $sellerPassportSeries;
        public $sellerPassportNumber;
        public $sellerPassportIssueDate;
        public $sellerPassportIssuingAuthority;
        public $sellerTin;
        public $sellerDateOfBirth;

        const PARAM_ID = [
            'cdek_official_seller_international_shipping_checkbox' => 'sellerInternationalShippingCheckbox',
            'cdek_official_shipping_seller_name' => 'shippingSellerName',
            'cdek_official_shipping_seller_phone' => 'shippingSellerPhone',
            'cdek_official_seller__true_seller_address' => 'sellerTrueSellerAddress',
            'cdek_official_seller__shipper' => 'sellerShipper',
            'cdek_official_seller__shipper_address' => 'sellerShipperAddress',
            'cdek_official_seller__passport_series' => 'sellerPassportSeries',
            'cdek_official_seller__passport_number' => 'sellerPassportNumber',
            'cdek_official_seller__passport_issue_date' => 'sellerPassportIssueDate',
            'cdek_official_seller__passport_issuing_authority' => 'sellerPassportIssuingAuthority',
            'cdek_official_seller__tin' => 'sellerTin',
            'cdek_official_seller__date_of_birth' => 'sellerDateOfBirth',
        ];

        /**
         * @throws Exception
         */
        public function validate()
        {
            if (empty($this->shippingSellerName)) {
                throw new Exception('cdek_error_shipping_seller_name_empty');
            }

            if (strlen($this->shippingSellerName) > 255) {
                throw new Exception('cdek_error_shipping_seller_name_too_long');
            }

            if (empty($this->shippingSellerPhone)) {
                throw new Exception('cdek_error_shipping_seller_phone_empty');
            }

            $phoneNumUtil = PhoneNumberUtil::getInstance();

            if (!$phoneNumUtil->isValidNumber($phoneNumUtil->parse($this->shippingSellerPhone))) {
                throw new Exception('cdek_error_shipping_seller_phone_invalid_format');
            }

            if ($this->sellerInternationalShippingCheckbox !== '1') {
                return;
            }

            if (empty($this->sellerTrueSellerAddress)) {
                throw new Exception('cdek_error_seller_true_seller_address_empty');
            }

            if (strlen($this->sellerTrueSellerAddress) > 255) {
                throw new Exception('cdek_error_seller_true_seller_address_length');
            }

            if (empty($this->sellerShipper)) {
                throw new Exception('cdek_error_seller_shipper_empty');
            }

            if (empty($this->sellerShipperAddress)) {
                throw new Exception('cdek_error_seller_shipper_address_empty');
            }

            if (empty($this->sellerPassportSeries)) {
                throw new Exception('cdek_error_seller_passport_series_empty');
            }

            if (empty($this->sellerPassportNumber)) {
                throw new Exception('cdek_error_seller_passport_number_empty');
            }

            if (empty($this->sellerPassportIssueDate)) {
                throw new Exception('cdek_error_seller_passport_issue_date_empty');
            }

            if (empty($this->sellerPassportIssuingAuthority)) {
                throw new Exception('cdek_error_seller_passport_issuing_authority_empty');
            }

            if (empty($this->sellerTin)) {
                throw new Exception('cdek_error_seller_tin_empty');
            }

            if (empty($this->sellerDateOfBirth)) {
                throw new Exception('cdek_error_seller_date_of_birth_empty');
            }
        }
    }
