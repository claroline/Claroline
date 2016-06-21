<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @param string $errorMsg
     * @param int    $errorCode
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
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }
}
