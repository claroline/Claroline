<?php

namespace Claroline\PluginBundle\Service\ApplicationManager\Exception;

use Claroline\CommonBundle\Exception\ClarolineException;

class ApplicationException extends ClarolineException
{
    const NON_EXISTENT_APPLICATION = 0;
    const NOT_ELIGIBLE_FOR_PLATFORM_INDEX = 1;
    const MULTIPLES_INDEX_APPLICATIONS = 2;
    const NOT_ELIGIBLE_FOR_CONNECTION_TARGET = 3;
    const MULTIPLES_CONNECTION_TARGET_APPLICATIONS = 2;
    
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}