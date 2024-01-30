<?php

namespace CDEK;

use CDEK\Helpers\LogHelper;
use CDEK\Models\Order;
use CDEK\Transport\HttpClient;
use JsonException;

class CdekApi
{
    private const TOKEN_PATH   = 'oauth/token?parameters';
    private const REGION_PATH  = 'location/cities';
    private const ORDERS_PATH  = 'orders/';
    private const PVZ_PATH     = 'deliverypoints';
    private const CALC_PATH    = 'calculator/tarifflist';
    private const WAYBILL_PATH = 'print/orders/';

    /**
     * @throws JsonException
     */
    final public static function checkAuth(): bool
    {
        return (bool)self::getToken();
    }

    /**
     * @throws JsonException
     */
    private static function getToken(): ?string
    {
        $response = HttpClient::sendRequest(self::getApiUrl(self::TOKEN_PATH),
                                            'POST',
                                            http_build_query(self::getAuthData()));
        return $response['access_token'] ?? null;
    }

    private static function getApiUrl(string $path): string
    {
        return (self::testModeActive() ? Config::API_TEST_URL : Config::API_URL) . $path;
    }

    final public static function testModeActive(): bool
    {
        return SettingsSingleton::getInstance()->authSettings->authTestMode === 'on';
    }

    private static function getAuthData(): array
    {
        return self::testModeActive() ? [
            'grant_type'    => 'client_credentials',
            'client_id'     => 'EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI',
            'client_secret' => 'PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG',
        ] : [
            'grant_type'    => 'client_credentials',
            'client_id'     => SettingsSingleton::getInstance()->authSettings->authId,
            'client_secret' => SettingsSingleton::getInstance()->authSettings->authSecret,
        ];
    }

    /**
     * @throws JsonException
     */
    final public function getOrderByUuid(string $uuid): object
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::ORDERS_PATH . $uuid), 'GET', $this->getToken());
    }

    /**
     * @throws JsonException
     */
    final public function getCity(string $city): object
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::REGION_PATH),
                                           'GET',
                                           self::getToken(),
                                           ['city' => $city, 'size' => 5]);
    }

    /**
     * @throws JsonException
     */
    final public function getOffices(array $param): string
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::PVZ_PATH),
                                           'GET',
                                           self::getToken(),
                                           $param,
                                           true);
    }

    /**
     * @throws JsonException
     */
    final public function calculate(array $data): object
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::CALC_PATH), 'POST', self::getToken(), $data);
    }

    /**
     * @throws JsonException
     */
    final public function createOrder(Order $order): object
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::ORDERS_PATH),
                                           'POST',
                                           self::getToken(),
                                           $order->getRequestData());
    }

    /**
     * @throws JsonException
     */
    final public function getCityByParam(string $city, string $postcode): object
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::REGION_PATH),
                                           'GET',
                                           self::getToken(),
                                           ['city' => $city, 'postal_code' => $postcode]);
    }

    /**
     * @throws JsonException
     */
    final public function deleteOrder(string $uuid): object
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::ORDERS_PATH . $uuid),
                                           'DELETE',
                                           self::getToken());
    }

    /**
     * @throws JsonException
     */
    final public function renderWaybill(string $uuid): void
    {
        $requestBill = HttpClient::sendCdekRequest(self::getApiUrl(self::WAYBILL_PATH),
                                                   'POST',
                                                   self::getToken(),
                                                   [
                                                           'orders'     => [
                                                               'order_uuid' => $uuid,
                                                           ],
                                                           'copy_count' => 2,
                                                       ]);
        LogHelper::write('RequestBill: ' . json_encode($requestBill, JSON_THROW_ON_ERROR));

        sleep(5);

        $result = HttpClient::sendCdekRequest(self::getApiUrl(self::WAYBILL_PATH . $requestBill->entity->uuid),
                                              'GET',
                                              self::getToken());
        LogHelper::write('Result: ' . json_encode($result, JSON_THROW_ON_ERROR));

        header('Content-type', 'application/pdf');
        header('Content-Disposition', 'inline; filename=waybill.pdf');

        echo HttpClient::sendCdekRequest($result->entity->url, 'GET', self::getToken(), null, true);
        exit();
    }
}
