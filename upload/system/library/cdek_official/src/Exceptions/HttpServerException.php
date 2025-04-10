<?php

namespace CDEK\Exceptions;

use CDEK\Contracts\ExceptionContract;

class HttpServerException extends ExceptionContract
{
    protected string $key = 'http.server';

    public function __construct(array $data)
    {
        $this->message = $this->message ?: 'Server request error';

        parent::__construct($data);
    }
}