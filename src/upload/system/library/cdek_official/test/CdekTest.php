<?php

require_once(DIR_SYSTEM . 'library/cdek_official/CdekApi.php');

//Добавить в admin/controller/extension/shipping/cdek_official.php после проверки авторизации $status = $cdekApi->checkAuth();
//$cdekTest = new CdekTest($this);
//$cdekTest->test();

class CdekTest
{
    protected $controller;
    protected $cdekApi;
    protected $message;

    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->cdekApi = new CdekApi($controller);
    }

    public function test(): bool
    {
        if (!$this->cdekApi->checkAuth()) {
           return false;
        }

        $this->message[] = $this->testGetOrderByUuid();
        $this->message[] = $this->testGetOrderByNumber();
        $this->message[] = $this->testGetCity();
        $this->message[] = $this->testGetPvz();
        $this->message[] = $this->testCalculate();

        var_dump($this->message);
        return true;
    }

    protected function testGetOrderByUuid()
    {
        $uuid = '72753034-15a6-46ab-906a-2a493b621414';
        $response = $this->cdekApi->getOrderByUuid($uuid);
        if ($response->requests[0]->state !== 'SUCCESSFUL') {
            return 'Test GetOrderByUuid was Fail';
        }
        return "Test GetOrderByUuid was successful. " . $response->entity->uuid;
    }

    protected function testGetOrderByNumber(): string
    {
        $number = '1461481873';
        $response = $this->cdekApi->getOrderByNumber($number);
        if ($response->requests[0]->state !== 'SUCCESSFUL') {
            return 'Test GetOrderByNumber was Fail';
        }
        return "Test GetOrderByNumber was successful. " . $response->entity->uuid;
    }

    protected function testGetCity(): string
    {
        $city = 'Москва';
        $response = $this->cdekApi->getCity($city);
        if (!is_array($response)) {
            return 'Test GetCity was Fail';
        }
        return "Test GetCity was successful. ";
    }

    protected function testGetPvz()
    {
        $cityCode = 44;
        $response = $this->cdekApi->getPvz($cityCode);
        if (!is_array($response) || empty($response)) {
            return 'Test GetPvz was Fail';
        }
        return "Test GetPvz was successful.";
    }

    protected function testCalculate()
    {
        $data['tariff'] = 136;
        $data['city_code_from'] = 44;
        $data['city_code_to'] = 137;
        $data['package_data']['weight'] = 2;
        $data['package_data']['length'] = 12;
        $data['package_data']['width'] = 12;
        $data['package_data']['height'] = 12;

        $response = $this->cdekApi->calculate($data);
        if (!property_exists($response, 'delivery_sum')) {
            return 'Test Calculate was Fail';
        }
        return "Test Calculate was successful.";
    }
}