<?php

class Settings
{
    public function init($data, $config)
    {
        $data['tariffs'] = Tariffs::tariffs;

        if (isset($this->request->post['cdek_auth_id'])) {
            $data['cdek_official_auth_id'] = $this->request->post['cdek_official_auth_id'];
        } else {
            $data['cdek_official_auth_id'] = $config->get('cdek_official_auth_id');
        }

        if (isset($this->request->post['cdek_auth_secret'])) {
            $data['cdek_official_auth_secret'] = $this->request->post['cdek_official_auth_secret'];
        } else {
            $data['cdek_official_auth_secret'] = $config->get('cdek_official_auth_secret');
        }

        if (isset($this->request->post['cdek_official_shipping_seller_name'])) {
            $data['cdek_official_shipping_seller_name'] = $this->request->post['cdek_official_shipping_seller_name'];
        } else {
            $data['cdek_official_shipping_seller_name'] = $config->get('cdek_official_shipping_seller_name');
        }

        if (isset($this->request->post['cdek_official_shipping_seller_phone'])) {
            $data['cdek_official_shipping_seller_phone'] = $this->request->post['cdek_official_shipping_seller_phone'];
        } else {
            $data['cdek_official_shipping_seller_phone'] = $config->get('cdek_official_shipping_seller_phone');
        }

        if (isset($this->request->post['cdek_official_seller_international_shipping_checkbox'])) {
            $data['cdek_official_seller_international_shipping_checkbox'] = $this->request->post['cdek_official_seller_international_shipping_checkbox'];
        } else {
            $data['cdek_official_seller_international_shipping_checkbox'] = $config->get('cdek_official_seller_international_shipping_checkbox');
        }

        if (isset($this->request->post['cdek_official_seller__true_seller_address'])) {
            $data['cdek_official_seller__true_seller_address'] = $this->request->post['cdek_official_seller__true_seller_address'];
        } else {
            $data['cdek_official_seller__true_seller_address'] = $config->get('cdek_official_seller__true_seller_address');
        }

        if (isset($this->request->post['cdek_official_seller__shipper'])) {
            $data['cdek_official_seller__shipper'] = $this->request->post['cdek_official_seller__shipper'];
        } else {
            $data['cdek_official_seller__shipper'] = $config->get('cdek_official_seller__shipper');
        }

        if (isset($this->request->post['cdek_official_seller__shipper_address'])) {
            $data['cdek_official_seller__shipper_address'] = $this->request->post['cdek_official_seller__shipper_address'];
        } else {
            $data['cdek_official_seller__shipper_address'] = $config->get('cdek_official_seller__shipper_address');
        }

        if (isset($this->request->post['cdek_official_seller__passport_series'])) {
            $data['cdek_official_seller__passport_series'] = $this->request->post['cdek_official_seller__passport_series'];
        } else {
            $data['cdek_official_seller__passport_series'] = $config->get('cdek_official_seller__passport_series');
        }

        if (isset($this->request->post['cdek_official_seller__passport_number'])) {
            $data['cdek_official_seller__passport_number'] = $this->request->post['cdek_official_seller__passport_number'];
        } else {
            $data['cdek_official_seller__passport_number'] = $config->get('cdek_official_seller__passport_number');
        }

        if (isset($this->request->post['cdek_official_seller__passport_issue_date'])) {
            $data['cdek_official_seller__passport_issue_date'] = $this->request->post['cdek_official_seller__passport_issue_date'];
        } else {
            $data['cdek_official_seller__passport_issue_date'] = $config->get('cdek_official_seller__passport_issue_date');
        }

        if (isset($this->request->post['cdek_official_seller__passport_issuing_authority'])) {
            $data['cdek_official_seller__passport_issuing_authority'] = $this->request->post['cdek_official_seller__passport_issuing_authority'];
        } else {
            $data['cdek_official_seller__passport_issuing_authority'] = $config->get('cdek_official_seller__passport_issuing_authority');
        }

        if (isset($this->request->post['cdek_official_seller__tin'])) {
            $data['cdek_official_seller__tin'] = $this->request->post['cdek_official_seller__tin'];
        } else {
            $data['cdek_official_seller__tin'] = $config->get('cdek_official_seller__tin');
        }

        if (isset($this->request->post['cdek_official_seller__date_of_birth'])) {
            $data['cdek_official_seller__date_of_birth'] = $this->request->post['cdek_official_seller__date_of_birth'];
        } else {
            $data['cdek_official_seller__date_of_birth'] = $config->get('cdek_official_seller__date_of_birth');
        }

        if (isset($this->request->post['cdek_official_shipping__tariff_name'])) {
            $data['cdek_official_shipping__tariff_name'] = $this->request->post['cdek_official_shipping__tariff_name'];
        } else {
            $data['cdek_official_shipping__tariff_name'] = $config->get('cdek_official_shipping__tariff_name');
        }

        if (isset($this->request->post['cdek_official_shipping__tariff_plug'])) {
            $data['cdek_official_shipping__tariff_plug'] = $this->request->post['cdek_official_shipping__tariff_plug'];
        } else {
            $data['cdek_official_shipping__tariff_plug'] = $config->get('cdek_official_shipping__tariff_plug');
        }

        if (isset($this->request->post['cdek_official_shipping__many_packages'])) {
            $data['cdek_official_shipping__many_packages'] = $this->request->post['cdek_official_shipping__many_packages'];
        } else {
            $data['cdek_official_shipping__many_packages'] = $config->get('cdek_official_shipping__many_packages');
        }

        if (isset($this->request->post['cdek_official_shipping__extra_days'])) {
            $data['cdek_official_shipping__extra_days'] = $this->request->post['cdek_official_shipping__extra_days'];
        } else {
            $data['cdek_official_shipping__extra_days'] = $config->get('cdek_official_shipping__extra_days');
        }

        if (isset($this->request->post['cdek_official_shipping__city'])) {
            $data['cdek_official_shipping__city'] = $this->request->post['cdek_official_shipping__city'];
        } else {
            $data['cdek_official_shipping__city'] = $config->get('cdek_official_shipping__city');
        }

        if (isset($this->request->post['cdek_official_shipping__city_address'])) {
            $data['cdek_official_shipping__city_address'] = $this->request->post['cdek_official_shipping__city_address'];
        } else {
            $data['cdek_official_shipping__city_address'] = $config->get('cdek_official_shipping__city_address');
        }

        if (isset($this->request->post['cdek_official_shipping__pvz'])) {
            $data['cdek_official_shipping__pvz'] = $this->request->post['cdek_official_shipping__pvz'];
        } else {
            $data['cdek_official_shipping__pvz'] = $config->get('cdek_official_shipping__pvz');
        }

        if (isset($this->request->post['cdek_official_dimensions__length'])) {
            $data['cdek_official_dimensions__length'] = $this->request->post['cdek_official_dimensions__length'];
        } else {
            $data['cdek_official_dimensions__length'] = $config->get('cdek_official_dimensions__length');
        }

        if (isset($this->request->post['cdek_official_dimensions__width'])) {
            $data['cdek_official_dimensions__width'] = $this->request->post['cdek_official_dimensions__width'];
        } else {
            $data['cdek_official_dimensions__width'] = $config->get('cdek_official_dimensions__width');
        }

        if (isset($this->request->post['cdek_official_dimensions__height'])) {
            $data['cdek_official_dimensions__height'] = $this->request->post['cdek_official_dimensions__height'];
        } else {
            $data['cdek_official_dimensions__height'] = $config->get('cdek_official_dimensions__height');
        }

        if (isset($this->request->post['cdek_official_dimensions__weight'])) {
            $data['cdek_official_dimensions__weight'] = $this->request->post['cdek_official_dimensions__weight'];
        } else {
            $data['cdek_official_dimensions__weight'] = $config->get('cdek_official_dimensions__weight');
        }

        if (isset($this->request->post['cdek_official_dimensions__use_default'])) {
            $data['cdek_official_dimensions__use_default'] = $this->request->post['cdek_official_dimensions__use_default'];
        } else {
            $data['cdek_official_dimensions__use_default'] = $config->get('cdek_official_dimensions__use_default');
        }

        if (isset($this->request->post['cdek_official_price__extra_price'])) {
            $data['cdek_official_price__extra_price'] = $this->request->post['cdek_official_price__extra_price'];
        } else {
            $data['cdek_official_price__extra_price'] = $config->get('cdek_official_price__extra_price');
        }

        if (isset($this->request->post['cdek_official_price__percentage_increase'])) {
            $data['cdek_official_price__percentage_increase'] = $this->request->post['cdek_official_price__percentage_increase'];
        } else {
            $data['cdek_official_price__percentage_increase'] = $config->get('cdek_official_price__percentage_increase');
        }

        if (isset($this->request->post['cdek_official_price__fix'])) {
            $data['cdek_official_price__fix'] = $this->request->post['cdek_official_price__fix'];
        } else {
            $data['cdek_official_price__fix'] = $config->get('cdek_official_price__fix');
        }

        if (isset($this->request->post['cdek_official_price__free'])) {
            $data['cdek_official_price__free'] = $this->request->post['cdek_official_price__free'];
        } else {
            $data['cdek_official_price__free'] = $config->get('cdek_official_price__free');
        }

        if (isset($this->request->post['cdek_official_price__insurance'])) {
            $data['cdek_official_price__insurance'] = $this->request->post['cdek_official_price__insurance'];
        } else {
            $data['cdek_official_price__insurance'] = $config->get('cdek_official_price__insurance');
        }
        return $data;
    }

    public function getSettings()
    {
        return $this->model_setting_setting->getSettings('cdek_official');
    }
}