<?php

namespace App\Exceptions;

class AejApiTimeoutException extends AejApiException
{
    protected $code = 504;
}
