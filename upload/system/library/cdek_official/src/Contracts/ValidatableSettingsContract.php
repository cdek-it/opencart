<?php

namespace CDEK\Contracts;

use ReflectionClass;
use ReflectionProperty;

abstract class ValidatableSettingsContract
{
    private const PARAM_PREFIX = 'cdek_official__';

    public function __construct(array $data = null)
    {
        if ($data) {
            $this->__unserialize($data);
        }
    }

    final public function __unserialize(array $post): void
    {
        $reflect = new ReflectionClass(static::class);
        foreach ($reflect->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $propertyName = $property->getName();
            $postProperty = self::PARAM_PREFIX . $propertyName;
            if (!isset($post[$postProperty])) {
                continue;
            }
            $this->$propertyName
                = ($post[$postProperty] === '' && $property->getType()->allowsNull()) ?
                null : $post[$postProperty];
        }
    }

    abstract public function validate(): void;

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
