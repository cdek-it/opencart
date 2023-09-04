<?php

class Settings
{
    public function init($controller, $data)
    {
        $data['tariffs'] = Tariffs::tariffs;

        if (isset($controller->request->post['cdek_auth_id'])) {
            $data['cdek_official_auth_id'] = $controller->request->post['cdek_official_auth_id'];
        } else {
            $data['cdek_official_auth_id'] = $controller->config->get('cdek_official_auth_id');
        }

        if (isset($controller->request->post['cdek_auth_secret'])) {
            $data['cdek_official_auth_secret'] = $controller->request->post['cdek_official_auth_secret'];
        } else {
            $data['cdek_official_auth_secret'] = $controller->config->get('cdek_official_auth_secret');
        }

        $data['cdek_official_auth__test_mode'] = $controller->config->get('cdek_official_auth__test_mode') !== null ? 1 : 0;

        if (isset($controller->request->post['cdek_official_shipping_seller_name'])) {
            $data['cdek_official_shipping_seller_name'] = $controller->request->post['cdek_official_shipping_seller_name'];
        } else {
            $data['cdek_official_shipping_seller_name'] = $controller->config->get('cdek_official_shipping_seller_name');
        }

        if (isset($controller->request->post['cdek_official_shipping_seller_phone'])) {
            $data['cdek_official_shipping_seller_phone'] = $controller->request->post['cdek_official_shipping_seller_phone'];
        } else {
            $data['cdek_official_shipping_seller_phone'] = $controller->config->get('cdek_official_shipping_seller_phone');
        }

        if (isset($controller->request->post['cdek_official_seller_international_shipping_checkbox'])) {
            $data['cdek_official_seller_international_shipping_checkbox'] = $controller->request->post['cdek_official_seller_international_shipping_checkbox'];
        } else {
            $data['cdek_official_seller_international_shipping_checkbox'] = $controller->config->get('cdek_official_seller_international_shipping_checkbox');
        }

        if (isset($controller->request->post['cdek_official_seller__true_seller_address'])) {
            $data['cdek_official_seller__true_seller_address'] = $controller->request->post['cdek_official_seller__true_seller_address'];
        } else {
            $data['cdek_official_seller__true_seller_address'] = $controller->config->get('cdek_official_seller__true_seller_address');
        }

        if (isset($controller->request->post['cdek_official_seller__shipper'])) {
            $data['cdek_official_seller__shipper'] = $controller->request->post['cdek_official_seller__shipper'];
        } else {
            $data['cdek_official_seller__shipper'] = $controller->config->get('cdek_official_seller__shipper');
        }

        if (isset($controller->request->post['cdek_official_seller__shipper_address'])) {
            $data['cdek_official_seller__shipper_address'] = $controller->request->post['cdek_official_seller__shipper_address'];
        } else {
            $data['cdek_official_seller__shipper_address'] = $controller->config->get('cdek_official_seller__shipper_address');
        }

        if (isset($controller->request->post['cdek_official_seller__passport_series'])) {
            $data['cdek_official_seller__passport_series'] = $controller->request->post['cdek_official_seller__passport_series'];
        } else {
            $data['cdek_official_seller__passport_series'] = $controller->config->get('cdek_official_seller__passport_series');
        }

        if (isset($controller->request->post['cdek_official_seller__passport_number'])) {
            $data['cdek_official_seller__passport_number'] = $controller->request->post['cdek_official_seller__passport_number'];
        } else {
            $data['cdek_official_seller__passport_number'] = $controller->config->get('cdek_official_seller__passport_number');
        }

        if (isset($controller->request->post['cdek_official_seller__passport_issue_date'])) {
            $data['cdek_official_seller__passport_issue_date'] = $controller->request->post['cdek_official_seller__passport_issue_date'];
        } else {
            $data['cdek_official_seller__passport_issue_date'] = $controller->config->get('cdek_official_seller__passport_issue_date');
        }

        if (isset($controller->request->post['cdek_official_seller__passport_issuing_authority'])) {
            $data['cdek_official_seller__passport_issuing_authority'] = $controller->request->post['cdek_official_seller__passport_issuing_authority'];
        } else {
            $data['cdek_official_seller__passport_issuing_authority'] = $controller->config->get('cdek_official_seller__passport_issuing_authority');
        }

        if (isset($controller->request->post['cdek_official_seller__tin'])) {
            $data['cdek_official_seller__tin'] = $controller->request->post['cdek_official_seller__tin'];
        } else {
            $data['cdek_official_seller__tin'] = $controller->config->get('cdek_official_seller__tin');
        }

        if (isset($controller->request->post['cdek_official_seller__date_of_birth'])) {
            $data['cdek_official_seller__date_of_birth'] = $controller->request->post['cdek_official_seller__date_of_birth'];
        } else {
            $data['cdek_official_seller__date_of_birth'] = $controller->config->get('cdek_official_seller__date_of_birth');
        }

        if (isset($controller->request->post['cdek_official_shipping__tariff_name'])) {
            $data['cdek_official_shipping__tariff_name'] = $controller->request->post['cdek_official_shipping__tariff_name'];
        } else {
            $data['cdek_official_shipping__tariff_name'] = $controller->config->get('cdek_official_shipping__tariff_name');
        }

        if (isset($controller->request->post['cdek_official_shipping__tariff_plug'])) {
            $data['cdek_official_shipping__tariff_plug'] = $controller->request->post['cdek_official_shipping__tariff_plug'];
        } else {
            $data['cdek_official_shipping__tariff_plug'] = $controller->config->get('cdek_official_shipping__tariff_plug');
        }

        if (isset($controller->request->post['cdek_official_shipping__many_packages'])) {
            $data['cdek_official_shipping__many_packages'] = $controller->request->post['cdek_official_shipping__many_packages'];
        } else {
            $data['cdek_official_shipping__many_packages'] = $controller->config->get('cdek_official_shipping__many_packages');
        }

        if (isset($controller->request->post['cdek_official_shipping__extra_days'])) {
            $data['cdek_official_shipping__extra_days'] = $controller->request->post['cdek_official_shipping__extra_days'];
        } else {
            $data['cdek_official_shipping__extra_days'] = $controller->config->get('cdek_official_shipping__extra_days');
        }

        if (isset($controller->request->post['cdek_official_shipping__city'])) {
            $data['cdek_official_shipping__city'] = $controller->request->post['cdek_official_shipping__city'];
        } else {
            $data['cdek_official_shipping__city'] = $controller->config->get('cdek_official_shipping__city');
        }

        if (isset($controller->request->post['cdek_official_shipping__city_address'])) {
            $data['cdek_official_shipping__city_address'] = $controller->request->post['cdek_official_shipping__city_address'];
        } else {
            $data['cdek_official_shipping__city_address'] = $controller->config->get('cdek_official_shipping__city_address');
        }

        if (isset($controller->request->post['cdek_official_shipping__pvz'])) {
            $data['cdek_official_shipping__pvz'] = $controller->request->post['cdek_official_shipping__pvz'];
        } else {
            $data['cdek_official_shipping__pvz'] = $controller->config->get('cdek_official_shipping__pvz');
        }

        if (isset($controller->request->post['cdek_official_dimensions__length'])) {
            $data['cdek_official_dimensions__length'] = $controller->request->post['cdek_official_dimensions__length'];
        } else {
            $data['cdek_official_dimensions__length'] = $controller->config->get('cdek_official_dimensions__length');
        }

        if (isset($controller->request->post['cdek_official_dimensions__width'])) {
            $data['cdek_official_dimensions__width'] = $controller->request->post['cdek_official_dimensions__width'];
        } else {
            $data['cdek_official_dimensions__width'] = $controller->config->get('cdek_official_dimensions__width');
        }

        if (isset($controller->request->post['cdek_official_dimensions__height'])) {
            $data['cdek_official_dimensions__height'] = $controller->request->post['cdek_official_dimensions__height'];
        } else {
            $data['cdek_official_dimensions__height'] = $controller->config->get('cdek_official_dimensions__height');
        }

        if (isset($controller->request->post['cdek_official_dimensions__weight'])) {
            $data['cdek_official_dimensions__weight'] = $controller->request->post['cdek_official_dimensions__weight'];
        } else {
            $data['cdek_official_dimensions__weight'] = $controller->config->get('cdek_official_dimensions__weight');
        }

        if (isset($controller->request->post['cdek_official_dimensions__use_default'])) {
            $data['cdek_official_dimensions__use_default'] = $controller->request->post['cdek_official_dimensions__use_default'];
        } else {
            $data['cdek_official_dimensions__use_default'] = $controller->config->get('cdek_official_dimensions__use_default');
        }

        if (isset($controller->request->post['cdek_official_price__extra_price'])) {
            $data['cdek_official_price__extra_price'] = $controller->request->post['cdek_official_price__extra_price'];
        } else {
            $data['cdek_official_price__extra_price'] = $controller->config->get('cdek_official_price__extra_price');
        }

        if (isset($controller->request->post['cdek_official_price__percentage_increase'])) {
            $data['cdek_official_price__percentage_increase'] = $controller->request->post['cdek_official_price__percentage_increase'];
        } else {
            $data['cdek_official_price__percentage_increase'] = $controller->config->get('cdek_official_price__percentage_increase');
        }

        if (isset($controller->request->post['cdek_official_price__fix'])) {
            $data['cdek_official_price__fix'] = $controller->request->post['cdek_official_price__fix'];
        } else {
            $data['cdek_official_price__fix'] = $controller->config->get('cdek_official_price__fix');
        }

        if (isset($controller->request->post['cdek_official_price__free'])) {
            $data['cdek_official_price__free'] = $controller->request->post['cdek_official_price__free'];
        } else {
            $data['cdek_official_price__free'] = $controller->config->get('cdek_official_price__free');
        }

        if (isset($controller->request->post['cdek_official_price__insurance'])) {
            $data['cdek_official_price__insurance'] = $controller->request->post['cdek_official_price__insurance'];
        } else {
            $data['cdek_official_price__insurance'] = $controller->config->get('cdek_official_price__insurance');
        }
        return $data;
    }

    public function getSettings()
    {
        return $this->model_setting_setting->getSettings('cdek_official');
    }
}