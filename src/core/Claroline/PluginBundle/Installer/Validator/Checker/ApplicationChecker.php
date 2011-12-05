<?php

namespace Claroline\PluginBundle\Installer\Validator\Checker;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\Exception\ValidationException;

class ApplicationChecker
{
    private $application;
    private $appFQCN;
    
    public function check(ClarolineApplication $application)
    {
        $this->application = $application;
        $this->appFQCN = get_class($application);
        
        $this->checkLaunchersAreValid();
        $this->checkIndexRouteIsValid();
        $this->checkIsEligibleForPlatformIndexValue();
        $this->checkIsEligibleForConnectionTargetValue();
    }
    
    private function checkLaunchersAreValid()
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
    
    private function checkIndexRouteIsValid()
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
    
    private function checkIsEligibleForPlatformIndexValue()
    {
        $isEligible = $this->application->isEligibleForPlatformIndex();
        
        if (! is_bool($isEligible))
        {
            throw new ValidationException(
                "{$this->appFQCN}::isEligibleForPlatformIndex() must return a boolean, " 
                . gettype($isEligible) . ' given.',
                ValidationException::INVALID_APPLICATION_IS_ELIGIBLE_INDEX_METHOD
            );
        }
    }
    
    private function checkIsEligibleForConnectionTargetValue()
    {
        $isEligible = $this->application->isEligibleForConnectionTarget();
        
        if (! is_bool($isEligible))
        {
            throw new ValidationException(
                "{$this->appFQCN}::isEligibleForConnectionTarget() must return a boolean, " 
                . gettype($isEligible) . ' given.',
                ValidationException::INVALID_APPLICATION_IS_ELIGIBLE_TARGET_METHOD
            );
        }
    }
}