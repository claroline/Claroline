<?php

namespace Claroline\CoreBundle\Exception;

use Claroline\CoreBundle\Exception\InstallationException;

class LoaderException extends InstallationException
{
    const NO_PLUGIN_FOUND = 0;
    const MULTIPLE_PLUGINS_FOUND = 1;
    const NO_AVAILABLE_PATH = 2;
    const NON_EXISTENT_BUNDLE_CLASS = 2;
    const NON_INSTANTIABLE_BUNDLE_CLASS = 2;
    
    public function __construct($message, $code)
    {
        parent::__construct($message, $code);
    }
}