<?php

namespace Claroline\CoreBundle\Exception;

use Claroline\CoreBundle\Exception\ClarolineException;

class InstallationException extends ClarolineException
{
    // loading exceptions
    const NO_PLUGIN_FOUND = 0;
    const MULTIPLE_PLUGINS_FOUND = 1;
    const NO_AVAILABLE_PATH = 2;
    const NON_EXISTENT_BUNDLE_CLASS = 3;
    const NON_INSTANTIABLE_BUNDLE_CLASS = 4;
    
    // validation exceptions
    const INVALID_FQCN = 5;
    const INVALID_PLUGIN_TYPE = 6;
    const INVALID_PLUGIN_LOCATION = 7;
    const INVALID_ROUTING_PREFIX = 8;
    const INVALID_ALREADY_REGISTERED_PREFIX = 9;
    const INVALID_ROUTING_PATH = 10;
    const INVALID_ROUTING_LOCATION = 11;
    const INVALID_ROUTING_EXTENSION = 12;
    const INVALID_YAML_RESOURCE = 13;
    const INVALID_TRANSLATION_KEY = 14;
    
    // general exceptions
    const UNEXPECTED_REGISTRATION_STATUS = 15;
    const EMPTY_FILE_ITEM = 16;
    const ENTIY_VALIDATION_ERROR = 17;
    
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}