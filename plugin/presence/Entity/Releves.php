<?php

namespace FormaLibre\PresenceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Releves
{
    private $releves;

    public function __construct()
    {
        $this->releves = new ArrayCollection();
    }
    /**
     * @param \FormaLibre\PresenceBundle\Entity\Releves $releves
     */
    public function setReleves($releves)
    {
        $this->releves = $releves;
    }
    /**
     * @return \FormaLibre\PresenceBundle\Entity\ArrayCollection
     */
    public function getReleves()
    {
        return $this->releves;
    }
}
