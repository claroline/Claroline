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
        $this->checkIndexRoute();
        $this->checkIsEligibleForPlatformIndex();
    }
    
    public function checkLaunchers()
    {
        $launchers = $this->application->getLaunchers();

        if (! is_array($launchers))
        {
            throw new ValidationException(
                "'{$this->appFQCN}::getLaunchers() must return an array.",
                ValidationException::INVALID_APPLICATION_GET_LAUNCHER_METHOD
            );
        }
        
        if (count($launchers) == 0)
        {
            throw new ValidationException(
                "Application '{$this->appFQCN}' must define at least one launcher.",
                ValidationException::INVALID_APPLICATION_LAUNCHER
            );
        }

        foreach ($launchers as $launcher)
        {
            if (! is_a($launcher, 'Claroline\PluginBundle\Widget\ApplicationLauncher'))
            {
                throw new ValidationException(
                    "Application '{$this->appFQCN}' has an invalid launcher.",
                    ValidationException::INVALID_APPLICATION_LAUNCHER
                );
            }
        }
    }
    
    private function checkIndexRoute()
    {
        $indexRoute = $this->application->getIndexRoute();
        
        if (! is_string($indexRoute))
        {
            throw new ValidationException(
                "{$this->appFQCN}::getRouteIndex() must return a string, " 
                . gettype($indexRoute) . ' given.',
                ValidationException::INVALID_APPLICATION_INDEX
            );
        }
        
        if (empty($indexRoute))
        {
            throw new ValidationException(
                "String value returned by {$this->appFQCN}::getRouteIndex() cannot be empty.",
                ValidationException::INVALID_APPLICATION_INDEX
            );
        }
        
        if (strlen($indexRoute) > 255)
        {
            throw new ValidationException(
                "String value returned by {$this->appFQCN}::getRouteIndex() "
                . 'exceeds the 255 characters limit.',
                ValidationException::INVALID_APPLICATION_INDEX
            );
        }
    }
    
    private function checkIsEligibleForPlatformIndex()
    {
        $isEligible = $this->application->isEligibleForPlatformIndex();
        
        if (!is_bool($isEligible))
        {
            throw new ValidationException(
                "{$this->appFQCN}::isEligibleForPlatformIndex() must return a boolean, " 
                . gettype($isEligible) . ' given.',
                ValidationException::INVALID_APPLICATION_IS_ELIGIBLE_METHOD
            );
        }
    }
}