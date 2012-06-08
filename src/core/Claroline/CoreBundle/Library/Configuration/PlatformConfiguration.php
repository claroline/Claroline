<?php

namespace Claroline\CoreBundle\Library\Configuration;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

class PlatformConfiguration
{
    /**/
    private $selfRegistration;
    
    private $localLanguage;
    
    public function __construct($selfRegistration, $localLanguage)
    {
        $this->selfRegistration=$selfRegistration;
        $this->localLanguage=$localLanguage;
    }
 
    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }
    
    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration=$selfRegistration;
    }
    
    public function getLocalLanguage()
    {
        return $this->localLanguage;
    }
    
    public function setLocalLanguage($localLanguage)
    {
        $this->localLanguage=$localLanguage;
    }
      

}

