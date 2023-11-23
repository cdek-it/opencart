<?php
namespace CDEK;

class CdekApiValidate
{
    public static function createApiValidate($response): bool
    {
        return $response->requests[0]->type === 'CREATE' && $response->requests[0]->state === 'ACCEPTED';
    }

    public static function deleteOrder($response): bool
    {
        return $response->requests[0]->state === 'ACCEPTED' || $response->requests[0]->state === 'SUCCESSFUL';
    }
}
