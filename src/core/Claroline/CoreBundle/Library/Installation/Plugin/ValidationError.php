<?php

namespace Claroline\CoreBundle\Library\Installation\Plugin;

/**
 * This class is used to store any error encountered during a plugin validation process.
 */
class ValidationError
{
    private $message;
    private $code;

    /**
     * Constructor.
     *
     * @param string  $errorMsg
     * @param integer $errorCode
     */
    public function __construct($message, $code = null)
    {
        $this->message = $message;
        $this->code = $code;
    }

    /**
     * Returns the error message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the error code.
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }
}