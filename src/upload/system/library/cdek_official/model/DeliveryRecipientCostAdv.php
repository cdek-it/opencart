<?php

class DeliveryRecipientCostAdv implements ApiParameter
{
    public int $threshold;
    public float $sum;
    public float $vatSum;
    public float $vatRate;

    public function __construct($threshold, $sum, $vatSum = '', $vatRate = '')
    {
        $this->threshold = $threshold;
        $this->sum = $sum;
        $this->vatSum = $vatSum;
        $this->vatRate = $vatRate;
    }

    public function validate()
    {
        if (!is_int($this->threshold) || $this->threshold <= 0) {
            throw new InvalidArgumentException("Threshold must be a positive integer");
        }

        if (!is_float($this->sum) || $this->sum <= 0) {
            throw new InvalidArgumentException("Sum must be a positive float");
        }

        if ($this->vatSum !== '' && (!is_float($this->vatSum) || $this->vatSum < 0)) {
            throw new InvalidArgumentException("VAT Sum must be a non-negative float");
        }

        if ($this->vatRate !== '' && (!is_float($this->vatRate) || $this->vatRate < 0)) {
            throw new InvalidArgumentException("VAT Rate must be a non-negative float");
        }
    }

    public function toArray(): array
    {
        return [
            'threshold' => $this->threshold,
            'sum' => $this->sum,
            'vat_sum' => $this->vatSum,
            'vat_rate' => $this->vatRate,
        ];
    }
}