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

        var_dump($this->message);
        return true;
    }

    protected function testGetOrderByUuid(): string
    {
        $uuid = '72753034-15a6-46ab-906a-2a493b621414';
        $response = $this->cdekApi->getOrderByUuid($uuid);
        if ($response->requests[0]->state !== 'SUCCESSFUL') {
            $this->fail('Test GetOrderByUuid was Fail');
        }
        return "Test GetOrderByUuid was successful. " . $response->entity->uuid;
    }

    protected function testGetOrderByNumber(): string
    {
        $number = '1461481873';
        $response = $this->cdekApi->getOrderByNumber($number);
        if ($response->requests[0]->state !== 'SUCCESSFUL') {
            $this->fail('Test GetOrderByNumber was Fail');
        }
        return "Test GetOrderByNumber was successful. " . $response->entity->uuid;
    }

    protected function testGetCity(): string
    {
        $city = 'Москва';
        $response = $this->cdekApi->getCity($city);
        if (!is_array($response)) {
            $this->fail('Test GetCity was Fail');
        }
        return "Test GetCity was successful. ";
    }

    protected function fail($message): void
    {
        $this->message[] = $message;
        var_dump($this->message);
        exit();
    }
}