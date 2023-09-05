<?php

class DeliveryRecipientCost
{
    public float $value;
    public float $vatSum;
    public int $vatRate;

    public function __construct($value, $vatSum = '', $vatRate = '')
    {
        $this->value = $value;
        $this->vatSum = $vatSum;
        $this->vatRate = $vatRate;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'vatSum' => $this->vatSum,
            'vatRate' => $this->vatRate,
        ];
    }
}