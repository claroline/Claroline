<?php

namespace Claroline\CoreBundle\Library\Configuration;

class PlatformConfiguration
{
    private $selfRegistration;
    private $localLanguage;

    public function __construct($selfRegistration, $localLanguage)
    {
        $this->selfRegistration = $selfRegistration;
        $this->localLanguage = $localLanguage;
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
}