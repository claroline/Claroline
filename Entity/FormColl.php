<?php

namespace FormaLibre\PresenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

class FormColl
{
    private $presFormColl;
   
    public function __construct()
    {
        $this->presFormColl = new ArrayCollection();
        
    }
    /**
     * @param \FormaLibre\PresenceBundle\Entity\FormColl $presFormColl
     */
    public function setPresFormColl($presFormColl)
    {
        $this->presFormColl = $presFormColl;
    }
    /**
     * @return \FormaLibre\PresenceBundle\Entity\ArrayCollection
     */
    public function getPresFormColl()
    {
        return $this->presFormColl;
    }
  
}

