<?php

namespace CDEK;

use CDEK\Helpers\LogHelper;
use Registry;

class CdekApi
{
    private const TOKEN_PATH    = 'oauth/token?parameters';
    private const REGION_PATH = 'location/cities';
    private const ORDERS_PATH = 'orders/';
    private const PVZ_PATH       = 'deliverypoints';
    private const CALC_PATH    = 'calculator/tarifflist';
    private const WAYBILL_PATH   = 'print/orders';
    private const API_URL      = 'https://api.cdek.ru/v2/';
    private const API_TEST_URL = 'https://api.edu.cdek.ru/v2/';
    private CdekHttpClient $httpClient;
    private Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->httpClient = new CdekHttpClient;
        $this->settings   = $settings;
    }

    public function checkAuth(): bool
    {
        $token = $this->getToken();
        if ($token) {
            return true;
        }
        return false;
    }

    protected function getToken()
    {
        return $this->httpClient->sendRequestAuth($this->getAuthUrl() . self::TOKEN_PATH, $this->getData());
    }

    private function getAuthUrl(): string
    {
        if ($this->testModeActive()) {
            return self::API_TEST_URL;
        }
        return self::API_URL;
    }

    public function testModeActive(): bool
    {
        return $this->settings->authSettings->authTestMode === 'on';
    }

    public function getData(): array
    {
        if ($this->testModeActive()) {
            $data = [
                'grant_type'    => 'client_credentials',
                'client_id'     => 'EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI',
                'client_secret' => 'PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG',
                'base_url'      => substr(self::API_TEST_URL, 0, -1)
            ];
        } else {
            $data = [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->settings->authSettings->authId,
                'client_secret' => $this->settings->authSettings->authSecret,
                'base_url'      => substr(self::API_URL, 0, -1)
            ];
        }
        return $data;
    }

    public function getOrderByUuid($uuid)
    {
        $url = $this->getAuthUrl() . self::ORDERS_PATH . $uuid;
        return $this->sendRequest($url, 'GET');
    }

    private function sendRequest(string $url, string $method, $data = null, $raw = false)
    {
        return $this->httpClient->sendRequest($url, $method, $this->getToken(), $data, $raw);
    }

    public function getCity($city)
    {
        $url = $this->getAuthUrl() . self::REGION_PATH;
        return $this->sendRequest($url, 'GET', ['city' => $city, 'size' => 5]);
    }

    public function getOffices($param)
    {
        return $this->sendRequest($this->getAuthUrl() . self::PVZ_PATH, 'GET', $param, true);
    }

    public function calculate($data)
    {
        $url = $this->getAuthUrl() . self::CALC_PATH;
        return $this->sendRequest($url, 'POST', $data);
    }

    public function createOrder($order)
    {
        $url = $this->getAuthUrl() . self::ORDERS_PATH;
        return $this->sendRequest($url, 'POST', $order->getRequestData());
    }

    public function getCityByParam($city, $postcode)
    {
        $url = $this->getAuthUrl() . self::REGION_PATH;
        return $this->sendRequest($url, 'GET', ['city' => $city, 'postal_code' => $postcode]);
    }

    public function deleteOrder($uuid)
    {
        $url = $this->getAuthUrl() . self::ORDERS_PATH . $uuid;
        return $this->sendRequest($url, 'DELETE');
    }

    public function getBill($uuid)
    {
        $url         = $this->getAuthUrl() . self::WAYBILL_PATH;
        $data        = [
            "orders"     => [
                "order_uuid" => $uuid
            ],
            "copy_count" => 2
        ];
        $requestBill = $this->sendRequest($url, 'POST', $data);
        LogHelper::write('RequestBill: ' . json_encode($requestBill));
        sleep(5);
        $result = $this->sendRequest($url . '/' . $requestBill->entity->uuid, 'GET');
        LogHelper::write('Result: ' . json_encode($result));
        header('Content-type', 'application/pdf');
        echo $this->httpClient->sendRequestBill($result->entity->url, $this->getToken());
        exit();
    }
}
