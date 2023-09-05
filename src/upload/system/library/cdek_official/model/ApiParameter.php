<?php

interface ApiParameter
{
    public function validate();
    public function toArray(): array;
}