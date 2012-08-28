<?php

namespace Claroline\CoreBundle\Library\Configuration;

class PlatformConfiguration
{
    private $selfRegistration;
    private $localLanguage;
    private $theme;

    public function __construct($selfRegistration, $localLanguage, $theme)
    {
        $this->selfRegistration = $selfRegistration;
        $this->localLanguage = $localLanguage;
        $this->theme = $theme;
    }

    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }

    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function getLocalLanguage()
    {
        return $this->localLanguage;
    }

    public function setLocalLanguage($localLanguage)
    {
        $this->localLanguage = $localLanguage;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
    }
}