<?php

class Order
{
    public int $type;
    public array $additionalOrderTypes;
    public string $number;
    public int $tariffCode;
    public string $comment;
    public string $developerKey;
    public string $shipmentPoint;
    public string $deliveryPoint;
    public string $dateInvoice;
    public string $shipperName;
    public string $shipperAddress;
    public DeliveryRecipientCost $deliveryRecipientCost;
    public DeliveryRecipientCostAdv $deliveryRecipientCostAdv;
    public array $sender;
    public array $seller;
    public array $recipient;
    public array $fromLocation;
    public array $toLocation;
    public array $service;
    public array $packages;
    public string $print;
    public bool $isClientReturn;
}