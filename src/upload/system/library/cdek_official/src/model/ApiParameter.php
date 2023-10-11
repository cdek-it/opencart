<?php
namespace CDEK\model;

interface ApiParameter
{
    public function validate();
    public function toArray(): array;
}