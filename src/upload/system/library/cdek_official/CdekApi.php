<?php

require_once(DIR_SYSTEM . 'library/cdek_official/CdekHttpClient.php');

class CdekApi
{
    protected const TOKEN_PATH = "oauth/token?parameters";
    protected const REGION_PATH = "location/cities";
    protected const ORDERS_PATH = "orders/";
    protected const PVZ_PATH = "deliverypoints";
    protected const CALC_PATH = "calculator/tariff";
    protected const WAYBILL_PATH = "print/orders/";
    protected const CALL_COURIER = "intakes";
    protected const API_URL = "https://api.cdek.ru/v2/";
    protected CdekHttpClient $httpClient;
    protected array $settings;
    protected $controller;

    public function __construct($controller)
    {
        $this->httpClient = new CdekHttpClient();
        $this->settings = $controller->model_setting_setting->getSetting('cdek_official');
        $this->controller = $controller;
    }

    protected function getToken()
    {
        $data = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->settings['cdek_official_auth_id'],
            'client_secret' => $this->settings['cdek_official_auth_secret']
        ];
        $token = $this->httpClient->sendRequestAuth(self::API_URL . self::TOKEN_PATH, $data);
        $this->controller->config->set('cdek_official_api_access_token', $token);
        return $token;
    }

    public function checkAuth(): bool
    {
        if ($this->getToken() !== false) {
            return true;
        }
        return false;
    }

    protected function sendRequestWithTokenRefresh($url, $method, $data = null)
    {
        $token = $this->controller->config->get('cdek_official_api_access_token');
        $response = $this->httpClient->sendRequest($url, $method, $token, $data);
        if (is_object($response) && property_exists($response, 'requests') && $response->requests[0]->type === 'AUTH' && $response->requests[0]->state === 'INVALID') {
            $this->getToken();
            $newToken = $this->controller->config->get('cdek_official_api_access_token');
            $response = $this->httpClient->sendRequest($url, $method, $newToken);
        }

        return $response;
    }

    public function getOrderByUuid($uuid)
    {
        $url = self::API_URL . self::ORDERS_PATH . $uuid;
        return $this->sendRequestWithTokenRefresh($url, 'GET');
    }

    public function getOrderByNumber($number)
    {
        $url = self::API_URL . self::ORDERS_PATH;
        return $this->sendRequestWithTokenRefresh($url, 'GET', ['cdek_number' => $number]);
    }

    public function getCity($city)
    {
        $url = self::API_URL . self::REGION_PATH;
        return $this->sendRequestWithTokenRefresh($url, 'GET', ['city' => $city]);
    }

    public function getPvz($cityCode, $weight = 0)
    {
        $url = self::API_URL . self::PVZ_PATH;

        $params['city_code'] = $cityCode;
        $params['weight_max'] = (int)ceil($weight);

        $result = $this->sendRequestWithTokenRefresh($url, 'GET', $params);
        $pvz = [];
        foreach ($result as $elem) {
            if (isset($elem->code, $elem->type, $elem->location->longitude, $elem->location->latitude, $elem->location->address)) {
                $pvz[] = [
                    'code' => $elem->code,
                    'type' => $elem->type,
                    'longitude' => $elem->location->longitude,
                    'latitude' => $elem->location->latitude,
                    'address' => $elem->location->address
                ];
            }
        }

        return $pvz;
    }

    public function calculate($data)
    {
        $url = self::API_URL . self::CALC_PATH;
        $param = [
            'tariff_code' => $data['tariff'],
            'from_location' => [
                'code' => $data['city_code_from']
            ],
            'to_location' => [
                'code' => $data['city_code_to'],
            ],
            'packages' => [
                'weight' => $data['package_data']['weight'],
                'length' => $data['package_data']['length'],
                'width' => $data['package_data']['width'],
                'height' => $data['package_data']['height'],
            ],
        ];
        return $this->sendRequestWithTokenRefresh($url, 'POST', $param);
    }
}