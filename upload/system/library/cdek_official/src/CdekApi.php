<?php

namespace CDEK;

use CDEK\Helpers\LogHelper;
use CDEK\Models\Order;
use JsonException;

class CdekApi
{
    private const TOKEN_PATH   = 'oauth/token?parameters';
    private const REGION_PATH  = 'location/cities';
    private const ORDERS_PATH  = 'orders/';
    private const PVZ_PATH     = 'deliverypoints';
    private const CALC_PATH    = 'calculator/tarifflist';
    private const WAYBILL_PATH = 'print/orders/';
    private const API_URL      = 'https://api.cdek.ru/v2/';
    private const API_TEST_URL = 'https://api.edu.cdek.ru/v2/';
    private Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    final public function checkAuth(): bool
    {
        return (bool)$this->getToken();
    }

    /**
     * @throws JsonException
     */
    private function getToken(): ?string
    {
        $response = CdekHttpClient::sendRequest($this->getApiUrl(self::TOKEN_PATH),
                                                'POST',
                                                http_build_query($this->getAuthData()));
        return $response['access_token'] ?? null;
    }

    private function getApiUrl(string $path): string
    {
        return ($this->testModeActive() ? self::API_TEST_URL : self::API_URL) . $path;
    }

    final public function testModeActive(): bool
    {
        return $this->settings->authSettings->authTestMode === 'on';
    }

    private function getAuthData(): array
    {
        return $this->testModeActive() ? [
            'grant_type'    => 'client_credentials',
            'client_id'     => 'EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI',
            'client_secret' => 'PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG',
            'base_url'      => substr(self::API_TEST_URL, 0, -1),
        ] : [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->settings->authSettings->authId,
            'client_secret' => $this->settings->authSettings->authSecret,
            'base_url'      => substr(self::API_URL, 0, -1),
        ];
    }

    final public function getOrderByUuid(string $uuid): object
    {
        return CdekHttpClient::sendCdekRequest($this->getApiUrl(self::ORDERS_PATH . $uuid), 'GET', $this->getToken());
    }

    final public function getCity(string $city): object
    {
        return CdekHttpClient::sendCdekRequest($this->getApiUrl(self::REGION_PATH),
                                               'GET',
                                               $this->getToken(),
                                               ['city' => $city, 'size' => 5]);
    }

    final public function getOffices(array $param): object
    {
        return CdekHttpClient::sendCdekRequest($this->getApiUrl(self::PVZ_PATH),
                                               'GET',
                                               $this->getToken(),
                                               $param,
                                               true);
    }

    final public function calculate(array $data): object
    {
        return CdekHttpClient::sendCdekRequest($this->getApiUrl(self::CALC_PATH), 'POST', $this->getToken(), $data);
    }

    final public function createOrder(Order $order): object
    {
        return CdekHttpClient::sendCdekRequest($this->getApiUrl(self::ORDERS_PATH),
                                               'POST',
                                               $this->getToken(),
                                               $order->getRequestData());
    }

    final public function getCityByParam(string $city, string $postcode): object
    {
        return CdekHttpClient::sendCdekRequest($this->getApiUrl(self::REGION_PATH),
                                               'GET',
                                               $this->getToken(),
                                               ['city' => $city, 'postal_code' => $postcode]);
    }

    final public function deleteOrder(string $uuid): object
    {
        return CdekHttpClient::sendCdekRequest($this->getApiUrl(self::ORDERS_PATH . $uuid),
                                               'DELETE',
                                               $this->getToken());
    }

    final public function renderWaybill(string $uuid): void
    {
        $requestBill = CdekHttpClient::sendCdekRequest($this->getApiUrl(self::WAYBILL_PATH),
                                                       'POST',
                                                       $this->getToken(),
                                                       [
                                                           'orders'     => [
                                                               'order_uuid' => $uuid,
                                                           ],
                                                           'copy_count' => 2,
                                                       ]);
        LogHelper::write('RequestBill: ' . json_encode($requestBill));

        sleep(5);

        $result = CdekHttpClient::sendCdekRequest($this->getApiUrl(self::WAYBILL_PATH . $requestBill->entity->uuid),
                                                  'GET',
                                                  $this->getToken());
        LogHelper::write('Result: ' . json_encode($result));

        header('Content-type', 'application/pdf');
        header('Content-Disposition', 'inline; filename=waybill.pdf');

        echo CdekHttpClient::sendCdekRequest($result->entity->url, 'GET', $this->getToken(), null, true);
        exit();
    }
}
