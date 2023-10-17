<?php

namespace CDEK\model;
use Exception;

class SettingsAuth extends AbstractSettings
{
    public $authId;
    public $authSecret;
    public $apiKey;
    public $mapLangCode;
    public $authTestMode;

    const PARAM_ID = [
        'cdek_official_auth_id' => 'authId',
        'cdek_official_auth_secret' => 'authSecret',
        'cdek_official_api_key' => 'apiKey',
        'cdek_official_map_lang_code' => 'mapLangCode',
        'cdek_official_auth__test_mode' => 'authTestMode',
    ];

    /**
     * @throws Exception
     */
    public function validate()
    {
        if ($this->authTestMode === 'on') {
            return;
        }

        if (empty($this->authId)) {
            throw new Exception('cdek_error_auth_id_empty');
        }

        if (empty($this->authSecret)) {
            throw new Exception('cdek_error_auth_secret_empty');
        }

        if (empty($this->apiKey)) {
            throw new Exception('cdek_error_auth_secret_empty');
        }
    }
}