<?php
/**
 * @license MIT
 *
 * Modified by learndash on 18-December-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace StellarWP\Learndash\Razorpay\Api\Errors;

use Exception;

class Error extends Exception
{
    protected $httpStatusCode;

    public function __construct($message, $code, $httpStatusCode)
    {
        $this->code = $code;

        $this->message = $message;

        $this->httpStatusCode = $httpStatusCode;
    }

    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }
}