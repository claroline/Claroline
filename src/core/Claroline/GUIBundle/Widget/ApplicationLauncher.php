<?php

namespace Claroline\GUIBundle\Widget;

class ApplicationLauncher
{
    private $routeId;
    private $translationKey;
    private $accessRoles;

    public function __construct($routeId, $translationKey, array $accessRoles)
    {
        $this->routeId = $routeId;
        $this->translationKey = $translationKey;
        $this->accessRoles = $accessRoles;
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
}