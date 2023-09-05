<?php

require_once(DIR_SYSTEM . 'library/cdek_official/model/SettingsValidate.php');

abstract class AbstractSettings implements SettingsValidate
{
    const PARAM_ID = [];

    public function init(array $post)
    {
        foreach (static::PARAM_ID as $key => $property) {
            if (isset($post[$key])) {
                $this->$property = $post[$key];
            }
        }
    }

    public function toArray(): array
    {
        $data = [];
        foreach (static::PARAM_ID as $key => $property) {
            $data[$key] = $this->$property;
        }
        return $data;
    }
}