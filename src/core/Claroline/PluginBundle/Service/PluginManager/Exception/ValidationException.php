<?php

namespace Claroline\PluginBundle\Service\PluginManager\Exception;

class ValidationException extends \Exception
{
    const INVALID_PLUGIN_DIR = 0;
    const INVALID_FQCN = 1;
    const INVALID_DIRECTORY_STRUCTURE = 2;
    const INVALID_PLUGIN_CLASS_FILE = 3;
    const INVALID_PLUGIN_CLASS = 4;
    const INVALID_PLUGIN_TYPE = 5;
    const INVALID_ROUTING_PREFIX = 6;
    const INVALID_ROUTING_PATH = 7;
    const INVALID_ROUTING_LOCATION = 8;
    const INVALID_ROUTING_EXTENSION = 9;
    const INVALID_YAML_RESOURCE = 10;
    const INVALID_TRANSLATION_KEY = 11;
    const INVALID_APPLICATION_GET_LAUNCHER_METHOD = 12;
    const INVALID_APPLICATION_LAUNCHER = 13;
    const INVALID_APPLICATION_INDEX = 14;
    const INVALID_APPLICATION_IS_ELIGIBLE_INDEX_METHOD = 15;
    const INVALID_APPLICATION_IS_ELIGIBLE_TARGET_METHOD = 16;

    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}