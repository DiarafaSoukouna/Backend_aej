<?php

namespace App\Exceptions;

class AejApiRateLimitException extends AejApiException
{
    protected $code = 429;
}
