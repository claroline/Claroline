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
    const INVALID_ROUTING_RESOURCES = 6;

    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}