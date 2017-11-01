<?php

namespace Phwoolcon\Auth\Adapter;

use RuntimeException;

class Exception extends RuntimeException
{
    const CODE_INVALID_USER_CREDENTIAL = 1;
    const CODE_USER_CREDENTIAL_REGISTERED = 2;
    const CODE_INVALID_PASSWORD = 3;
    const CODE_UNABLE_TO_SAVE_USER = 4;
    const CODE_USER_NOT_FOUND = 5;
    const CODE_RESET_PASSWORD_TOKEN_NOT_MATCH = 6;
    const CODE_RESET_PASSWORD_TOKEN_OUTDATED = 7;

    protected $realMessage;

    public function __construct($message, $code = 0, $realMessage = null)
    {
        $this->realMessage = $realMessage;
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        if ($this->realMessage) {
            $message = $this->message;
            $this->message .= ': ' . $this->realMessage;
            $string = parent::__toString();
            $this->message = $message;
            return $string;
        }
        return parent::__toString();
    }
}
