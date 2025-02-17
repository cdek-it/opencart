<?php

namespace CDEK\Models\Settings;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;

class SettingsLogger extends ValidatableSettingsContract
{
    public bool $logMode = false;

    /**
     * @throws Exception
     */
    public function validate(): void
    {}
}
