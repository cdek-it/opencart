<?php

namespace CDEK\Exceptions;

class UnparsableAnswerException extends HttpServerException
{
    protected string $key = 'api.parse';

    public function __construct(string $answer, string $url, string $method)
    {
        $this->message = $this->message ?: 'Unable to parse API answer';

        parent::__construct(['answer' => $answer, 'url' => $url, 'method' => $method]);
    }
}