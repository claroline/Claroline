<?php

namespace Claroline\SecurityBundle\Service\Exception;

use Claroline\CommonBundle\Exception\ClarolineException;

class RightManagerException extends ClarolineException
{
    const INVALID_ENTITY_STATE = 1;
    const INVALID_USER_STATE = 2;
    const INVALID_PERMISSION_MASK = 3;
    const INVALID_PERMISSION = 4;
    const NO_GET_ID_METHOD = 5;
    const MULTIPLE_OWNERS_ENTITY = 6;
    const MULTIPLE_OWNERS_ATTEMPT = 7;
    
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}