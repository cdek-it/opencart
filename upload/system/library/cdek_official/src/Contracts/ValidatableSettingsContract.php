<?php

namespace CDEK\Contracts;

use ReflectionClass;
use ReflectionProperty;

abstract class ValidatableSettingsContract
{
    private const PARAM_PREFIX = 'cdek_official__';

    abstract public function validate(): void;

    public function __construct(array $data = null) {
        if($data){
            $this->__unserialize($data);
        }
    }

    final public function __unserialize(array $post): void
    {
        $reflect = new ReflectionClass(static::class);
        foreach ($post as $key => $property) {
            if (strpos($key, self::PARAM_PREFIX) !== 0) {
                continue;
            }
            $propertyName = substr($key, strlen(self::PARAM_PREFIX));
            if (!$reflect->hasProperty($propertyName)) {
                continue;
            }
            $this->$propertyName = $property;
        }
    }

    final public function __serialize(): array
    {
        $reflect = new ReflectionClass(static::class);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        $data    = [];
        foreach ($props as $property) {
            $data[self::PARAM_PREFIX . $property->getName()] = $property->getValue($this);
        }
        return $data;
    }
}
