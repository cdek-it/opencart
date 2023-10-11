<?php
namespace CDEK;

class CdekApiValidate
{
    public static function createApiValidate($response): bool
    {
        if ($response->requests[0]->type === 'CREATE' && $response->requests[0]->state === 'ACCEPTED') {
            return true;
        }
        return false;
    }

    public static function deleteOrder($response): bool
    {
        if ($response->requests[0]->state === 'ACCEPTED' || $response->requests[0]->state === 'SUCCESSFUL') {
            return true;
        }
        return false;
    }
}