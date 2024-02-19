<?php

namespace CDEK\Models\Settings;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;
use RuntimeException;

class SettingsAuth extends ValidatableSettingsContract
{
    public string $authId = '';
    public string $authSecret = '';
    public string $apiKey = '';
    public string $mapLangCode = 'rus';
    public string $authTestMode = 'off';

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
