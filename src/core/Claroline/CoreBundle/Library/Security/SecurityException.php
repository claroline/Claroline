<?php

namespace Claroline\CoreBundle\Library\Security;

use \Exception;

class SecurityException extends Exception
{
    // right manager exceptions
    const INVALID_ENTITY_STATE = 1;
    const INVALID_USER_STATE = 2;
    const INVALID_ROLE_STATE = 3;
    const INVALID_PERMISSION_MASK = 4;
    const INVALID_PERMISSION = 5;
    const NO_GET_ID_METHOD = 6;
    const MULTIPLE_OWNERS_ENTITY = 7;
    const MULTIPLE_OWNERS_ATTEMPT = 8;
    const NOT_ALLOWED_OWNER_MASK = 9;

    // role exceptions
    const ROLE_EXCEPTION = 10;
}