<?php

class Phone implements ApiParameter
{
    public string $number;
    public string $additional;

    public function __construct($number, $additional = '')
    {
        $this->number = $number;
        $this->additional = $additional;
    }

    public function validate()
    {
        if (empty($this->number)) {
            throw new InvalidArgumentException("Number cannot be empty");
        }

        if (!preg_match('/^\+?[0-9]+$/', $this->number)) {
            throw new InvalidArgumentException("Invalid phone number format");
        }

        if (!empty($this->additional) && !is_string($this->additional)) {
            throw new InvalidArgumentException("Additional must be a string");
        }
    }

    public function toArray(): array
    {
        $this->validate();

        $array = [
            'number' => $this->number
        ];

        if (!empty($this->additional)) {
            $array['additional'] = $this->additional;
        }

        return $array;
    }
}