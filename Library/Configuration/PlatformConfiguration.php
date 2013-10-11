<?php

namespace Claroline\CoreBundle\Library\Configuration;

class PlatformConfiguration
{
    private $name;
    private $selfRegistration;
    private $localLanguage;
    private $theme;
    private $footer;

    public function __construct($name, $footer, $selfRegistration, $localLanguage, $theme)
    {
        $this->name = $name;
        $this->footer = $footer;
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

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getFooter()
    {
        return $this->footer;
    }

    public function setFooter($footer)
    {
        $this->footer = $footer;
    }
}
