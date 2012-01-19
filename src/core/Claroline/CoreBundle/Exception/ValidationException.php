<?php

namespace Claroline\CoreBundle\Exception;

use Claroline\CoreBundle\Exception\InstallationException;

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

    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}