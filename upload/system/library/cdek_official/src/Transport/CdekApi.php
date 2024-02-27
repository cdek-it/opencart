<?php

namespace CDEK\Transport;

use CDEK\Config;
use CDEK\Helpers\LogHelper;
use CDEK\SettingsSingleton;
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

    public static function testModeActive(): bool
    {
        return SettingsSingleton::getInstance()->authSettings->authTestMode;
    }

    private static function getAuthData(): array
    {
        return self::testModeActive() ? [
            'grant_type'    => 'client_credentials',
            'client_id'     => Config::TEST_ACCOUNT,
            'client_secret' => Config::TEST_SECRET,
        ] : [
            'grant_type'    => 'client_credentials',
            'client_id'     => SettingsSingleton::getInstance()->authSettings->authId,
            'client_secret' => SettingsSingleton::getInstance()->authSettings->authSecret,
        ];
    }

    /**
     * @throws JsonException
     */
    public static function getOrderByUuid(string $uuid): array
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::ORDERS_PATH . $uuid), 'GET', self::getToken());
    }

    /**
     * @throws JsonException
     */
    public static function getCity(string $city): array
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::REGION_PATH),
                                           'GET',
                                           self::getToken(),
                                           ['city' => $city, 'size' => 5]);
    }

    /**
     * @throws JsonException
     */
    public static function getOffices(array $param): string
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
    public static function calculate(array $data): array
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::CALC_PATH), 'POST', self::getToken(), $data);
    }

    /**
     * @throws JsonException
     */
    public static function createOrder(array $requestData): array
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::ORDERS_PATH),
                                           'POST',
                                           self::getToken(),
                                           $requestData);
    }

    /**
     * @throws JsonException
     */
    public static function getCityByParam(string $city, string $postcode): array
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::REGION_PATH),
                                           'GET',
                                           self::getToken(),
                                           ['city' => $city, 'postal_code' => $postcode]);
    }

    /**
     * @throws JsonException
     */
    public static function deleteOrder(string $uuid): array
    {
        return HttpClient::sendCdekRequest(self::getApiUrl(self::ORDERS_PATH . $uuid),
                                           'DELETE',
                                           self::getToken());
    }

    /**
     * @throws JsonException
     */
    public static function getWaybill(string $orderUuid): ?string
    {
        $requestBill = HttpClient::sendCdekRequest(self::getApiUrl(self::WAYBILL_PATH),
                                                   'POST',
                                                   self::getToken(),
                                                   [
                                                       'orders'     => [
                                                           'order_uuid' => $orderUuid,
                                                       ],
                                                       'copy_count' => 2,
                                                   ]);
        LogHelper::write('RequestBill: ' . json_encode($requestBill, JSON_THROW_ON_ERROR));

        sleep(5);

        $result = HttpClient::sendCdekRequest(self::getApiUrl(self::WAYBILL_PATH . $requestBill['entity']['uuid']),
                                              'GET',
                                              self::getToken());
        LogHelper::write('Result: ' . json_encode($result, JSON_THROW_ON_ERROR));

        if(empty($result['entity']['url'])){
            return null;
        }

        return HttpClient::sendCdekRequest($result['entity']['url'], 'GET', self::getToken(), null, true);
    }
}
