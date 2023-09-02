<?php

require_once(DIR_SYSTEM . 'library/cdek_official/CdekApi.php');

class CdekTest
{
    public function test($controller)
    {
        $cdekApi = new CdekApi($controller);
        $this->testGetOrderByUuid($cdekApi);
    }

    protected function testGetOrderByUuid(CdekApi $cdekApi)
    {
        $uuid = '72753034-15a6-46ab-906a-2a493b621414';
        $response = $cdekApi->getOrderByUuid($uuid);
        if ($response->requests[0]->state === 'SUCCESSFUL') {
            print "Test GetOrderByUuid was successful. " . $response->entity->uuid;
        } else {
            print 'Test GetOrderByUuid was Fail';
        }
    }
}