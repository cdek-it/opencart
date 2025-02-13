<?php

namespace CDEK\Models\Settings;

use CDEK\Contracts\ValidatableSettingsContract;
use Exception;
use RuntimeException;

class SettingsLogger extends ValidatableSettingsContract
{
    public ?int $logMode = null;

    /**
     * @throws Exception
     */
    public function validate(): void
    {
        if (!in_array((int)$this->logMode, [0, 1, null], true)) {
            throw new RuntimeException('cdek_error_log_mode_invalid');
        }
    }
}
