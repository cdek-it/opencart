<?php

namespace CDEK\Contracts;

abstract class ValidatableSettingsContract
{
    abstract public function validate(): void;

    protected const PARAM_ID = [];

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
