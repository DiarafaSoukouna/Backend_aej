<?php

namespace App\Exceptions;

class AejApiUnavailableException extends AejApiException
{
    protected $code = 503;
}
