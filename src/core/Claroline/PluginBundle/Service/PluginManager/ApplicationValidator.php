<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException;

class ApplicationValidator
{
    private $application;
    private $appFQCN;
    
    public function check(ClarolineApplication $application)
    {
        $this->application = $application;
        $this->appFQCN = get_class($application);
        
        $this->checkLaunchers();
    }
    
    public function checkLaunchers()
    {
        $launchers = (array) $this->application->getLaunchers();

        if (count($launchers) == 0)
        {
            throw new ValidationException(
                "Application '{$this->appFQCN}' must define at least one launcher.",
                ValidationException::INVALID_APPLICATION_LAUNCHER
            );
        }

        foreach ($launchers as $launcher)
        {
            if (! is_a($launcher, 'Claroline\GUIBundle\Widget\ApplicationLauncher'))
            {
                throw new ValidationException(
                    "Application '{$this->appFQCN}' has an invalid launcher.",
                    ValidationException::INVALID_APPLICATION_LAUNCHER
                );
            }
        }
    }
}