<?php

declare(strict_types=1);

namespace FFGBSY\Application\Exceptions;

class HttpBadRequestException extends HttpBaseException
{
    protected $message = 'Unknown';
    protected $code = 400;

    public function __construct($message = null)
    {
        parent::__construct($message, $this->code);
    }
}