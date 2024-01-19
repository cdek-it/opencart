<?php

namespace CDEK\Models;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;
use RuntimeException;

class SettingsAuth extends ValidatableSettingsContract
{
    const PARAM_ID
        = [
            'cdek_official_auth_id'         => 'authId',
            'cdek_official_auth_secret'     => 'authSecret',
            'cdek_official_api_key'         => 'apiKey',
            'cdek_official_map_lang_code'   => 'mapLangCode',
            'cdek_official_auth__test_mode' => 'authTestMode',
        ];
    public $authId;
    public $authSecret;
    public $apiKey;
    public $mapLangCode;
    public $authTestMode;

    /**
     * @throws Exception
     */
    final public function validate(): void
    {
        if ($this->authTestMode === 'on') {
            return;
        }

        if (empty($this->authId)) {
            throw new RuntimeException('cdek_error_auth_id_empty');
        }

        if (empty($this->authSecret)) {
            throw new RuntimeException('cdek_error_auth_secret_empty');
        }

        if (empty($this->apiKey)) {
            throw new RuntimeException('cdek_error_auth_secret_empty');
        }
    }
}
