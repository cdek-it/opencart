<?php

require_once(DIR_SYSTEM . 'library/cdek_official/CdekHttpClient.php');
require_once(DIR_SYSTEM . 'library/cdek_official/Settings.php');

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
    protected const API_TEST_URL = "https://api.edu.cdek.ru/v2/";
    protected CdekHttpClient $httpClient;
    protected Settings $settings;
    protected Registry $registry;

    public function __construct($registry, $settings)
    {
        $this->httpClient = new CdekHttpClient();
        $this->settings = $settings;
        $this->registry = $registry;
    }

    protected function getToken()
    {
        $data = $this->getData();
        $token = $this->fetchToken($data);
        $this->storeToken($token);
        return $token;
    }

    private function fetchToken($data)
    {
        return $this->httpClient->sendRequestAuth($this->getAuthUrl() . self::TOKEN_PATH, $data);
    }

    private function storeToken($token)
    {
        $this->registry->get('config')->set('cdek_official_api_access_token', $token);
    }

    protected function sendRequestWithTokenRefresh($url, $method, $data = null)
    {
        $token = $this->getStoredToken();
        $response = $this->httpClient->sendRequest($url, $method, $token, $data);

        if ($this->isTokenInvalid($response)) {
            $this->refreshToken();
            $newToken = $this->getStoredToken();
            $response = $this->httpClient->sendRequest($url, $method, $newToken, $data);
        }

        return $response;
    }

    private function getStoredToken()
    {
        return $this->registry->get('config')->get('cdek_official_api_access_token');
    }

    private function isTokenInvalid($response)
    {
        return is_object($response) &&
            property_exists($response, 'requests') &&
            $response->requests[0]->type === 'AUTH' &&
            $response->requests[0]->state === 'INVALID';
    }

    private function refreshToken()
    {
        $this->getToken();
    }

    public function checkAuth(): bool
    {
        if ($this->getToken() !== false) {
            return true;
        }
        return false;
    }

    public function testModeActive(): bool
    {
        if ($this->settings->authSettings->authTestMode === 'on') {
            return true;
        }
        return false;
    }

    public function getOrderByUuid($uuid)
    {
        $url = $this->getAuthUrl() . self::ORDERS_PATH . $uuid;
        return $this->sendRequestWithTokenRefresh($url, 'GET');
    }

    public function getOrderByNumber($number)
    {
        $url = $this->getAuthUrl() . self::ORDERS_PATH;
        return $this->sendRequestWithTokenRefresh($url, 'GET', ['cdek_number' => $number]);
    }

    public function getCity($city)
    {
        $url = $this->getAuthUrl() . self::REGION_PATH;
        return $this->sendRequestWithTokenRefresh($url, 'GET', ['city' => $city, 'size' => 5]);
    }

    public function getPvz($cityCode, $street, $weight = 0)
    {
        $url = $this->getAuthUrl() . self::PVZ_PATH;

        $params['city_code'] = $cityCode;
        $params['weight_min'] = (int)ceil($weight);

        $result = $this->sendRequestWithTokenRefresh($url, 'GET', $params);
        $pvz = [];
        foreach ($result as $elem) {
            if (isset($elem->code, $elem->type, $elem->location->longitude, $elem->location->latitude, $elem->location->address)) {
                if (strpos(mb_strtolower($elem->location->address), mb_strtolower($street)) !== false) {
                    $pvz[] = [
                        'code' => $elem->code,
                        'type' => $elem->type,
                        'longitude' => $elem->location->longitude,
                        'latitude' => $elem->location->latitude,
                        'address' => $elem->location->address
                    ];
                }
            }
        }

        return $pvz;
    }

    public function getPvzByCityCode($cityCode)
    {
        $url = $this->getAuthUrl() . self::PVZ_PATH;

        $params['city_code'] = $cityCode;

        $pvz = $this->sendRequestWithTokenRefresh($url, 'GET', $params);

        $result = [];
        foreach ($pvz as $elem) {
            if (isset($elem->type, $elem->location->country_code, $elem->have_cashless, $elem->have_cash,
                $elem->allowed_cod, $elem->is_dressing_room, $elem->code, $elem->name,
                $elem->location->address, $elem->work_time, $elem->location->longitude, $elem->location->latitude)) {
                $result[] = [
                    'city_code' => $cityCode,
                    'type' => $elem->type,
                    'country_code' => $elem->location->country_code,
                    'have_cashless' => $elem->have_cashless ? 1 : 0,
                    'have_cash' => $elem->have_cash ? 1 : 0,
                    'allowed_cod' => $elem->allowed_cod ? 1 : 0,
                    'is_dressing_room' => $elem->is_dressing_room ? 1 : 0,
                    'code' => $elem->code,
                    'name' => $elem->name,
                    'address' => $elem->location->address,
                    'work_time' => $elem->work_time,
                    'location' => [$elem->location->longitude, $elem->location->latitude]
                ];
            }
        }

        return $result;
    }

    public function calculate($data)
    {
        $url = $this->getAuthUrl() . self::CALC_PATH;
        return $this->sendRequestWithTokenRefresh($url, 'POST', $data);
    }

    public function createOrder($order)
    {
        $url = $this->getAuthUrl() . self::ORDERS_PATH;
        return $this->sendRequestWithTokenRefresh($url, 'POST', $order->getRequestData());
    }

    private function getAuthUrl(): string
    {
        if ($this->testModeActive()) {
            return self::API_TEST_URL;
        }
        return self::API_URL;
    }

    private function getData(): array
    {
        if ($this->testModeActive()) {
            $data = [
                'grant_type' => 'client_credentials',
                'client_id' => 'EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI',
                'client_secret' => 'PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG'
            ];
        } else {
            $data = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->settings->authSettings->authId,
                'client_secret' => $this->settings->authSettings->authSecret
            ];
        }
        return $data;
    }

    public function getCityByPostcode($postcode)
    {
        $url = $this->getAuthUrl() . self::REGION_PATH;
        return $this->sendRequestWithTokenRefresh($url, 'GET', ['postal_code' => $postcode]);
    }

    public function deleteOrder($uuid)
    {
        $url = $this->getAuthUrl() . self::ORDERS_PATH . $uuid;
        return $this->sendRequestWithTokenRefresh($url, 'DELETE');
    }
}