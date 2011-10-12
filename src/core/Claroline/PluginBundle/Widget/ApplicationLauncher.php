<?php

namespace Claroline\PluginBundle\Widget;

use Claroline\CommonBundle\Exception\ClarolineException;

class ApplicationLauncher
{
    private $routeId;
    private $translationKey;
    private $accessRoles;

    public function __construct($routeId, $translationKey, array $accessRoles)
    {
        $this->routeId = $this->checkString($routeId, 'Route id');
        $this->translationKey = $this->checkString($translationKey, 'Translation key');
        $this->accessRoles = $this->checkAccessRoles($accessRoles);
    }

    public function getRouteId()
    {
        return $this->routeId;
    }

    public function getTranslationKey()
    {
        return $this->translationKey;
    }

    public function getAccessRoles()
    {
        return $this->accessRoles;
    }

    private function checkAccessRoles($accessRoles)
    {
        if (count($accessRoles) === 0)
        {
            throw new ClarolineException(
                'Launcher must use at least one role, empty array given.'
            );
        }
        
        return $accessRoles;
    }
    
    private function checkString($string, $type)
    {
        if (! is_string($string))
        {
            throw new ClarolineException(
                "{$type} must be a string, " . gettype($string) . ' given.'
            );
        }
        
        if (empty($string))
        {
            throw new ClarolineException("{$type} cannot be empty.");
        }
        
        if (strlen($string) > 255)
        {
            throw new ClarolineException(
                "{$type} '{$string}' exceeds the 255 characters limit."
            );
        }
        
        return $string;
    }
}