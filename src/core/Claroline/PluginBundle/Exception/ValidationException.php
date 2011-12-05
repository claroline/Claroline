<?php

namespace Claroline\PluginBundle\Exception;

use Claroline\PluginBundle\Exception\InstallationException;

class ValidationException extends InstallationException
{
    const INVALID_FQCN = 0;
    const INVALID_PLUGIN_TYPE = 1;
    const INVALID_PLUGIN_LOCATION = 2;
    const INVALID_ROUTING_PREFIX = 3;
    const INVALID_ALREADY_REGISTERED_PREFIX = 4;
    const INVALID_ROUTING_PATH = 5;
    const INVALID_ROUTING_LOCATION = 6;
    const INVALID_ROUTING_EXTENSION = 7;
    const INVALID_YAML_RESOURCE = 8;
    const INVALID_TRANSLATION_KEY = 9;
    
    const INVALID_APPLICATION_GET_LAUNCHER_METHOD = 10;
    const INVALID_APPLICATION_LAUNCHER = 11;
    const INVALID_APPLICATION_INDEX = 12;
    const INVALID_APPLICATION_IS_ELIGIBLE_INDEX_METHOD = 13;
    const INVALID_APPLICATION_IS_ELIGIBLE_TARGET_METHOD = 14;

    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}