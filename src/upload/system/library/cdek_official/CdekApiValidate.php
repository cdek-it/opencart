<?php

class CdekApiValidate
{
    public static function createApiValidate($response): bool
    {
        if ($response->requests[0]->type === 'CREATE' && $response->requests[0]->state === 'ACCEPTED') {
            return true;
        }
        return false;
    }
}