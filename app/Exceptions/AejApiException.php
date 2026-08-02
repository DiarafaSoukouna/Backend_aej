<?php

namespace App\Exceptions;

use Exception;

class AejApiException extends Exception
{
    protected $code = 500;
}
