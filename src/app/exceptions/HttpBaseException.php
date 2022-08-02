<?php

declare(strict_types=1);

namespace FFGBSY\Application\Exceptions;

use \Exception;

class HttpBaseException extends Exception
{
    protected $message = 'Unknown';
    protected $code = 500;

    public function __construct($message = null, $code = 500)
    {
        if (!$message) {
            throw new $this('Unknown '. get_class($this));
        }
        parent::__construct($message, $code);
    }
}