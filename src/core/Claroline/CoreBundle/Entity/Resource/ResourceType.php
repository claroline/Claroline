<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_type")
 */
class ResourceType
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */    
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;
    
      /**
     * @ORM\Column(type="string", length=255)
     */
    private $bundle;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $controller;
    
    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource", mappedBy="resource_type")
     */
    private $resources;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $isListable;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $isNavigable;
    
    public function __construct()
    {
        $this->resources = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type=$type;
    }
    
    public function getController()
    {
        return $this->controller;
    }
    
    public function setController($controller)
    {
        $this->controller=$controller;
    }
    
    public function getResources()
    {
        return $this->resources;
    }
    
    public function addResource(AbstractResource $resource)
    {
        $this->resources[] = $resource;
        $resource->setUser($this);
    }
    
    public function isNavigable()
    {
        return $this->isNavigable;
    }
    
    public function isListable()
    {
        return $this->isListable;
    }
    
    public function setNavigable($bool)
    {
        $this->isNavigable=$bool;
    }
            
    public function setListable($bool)
    {
        $this->isListable=$bool;
    }    
    
    public function setBundle($bundle)
    {
        $this->bundle=$bundle;
    }
    
    public function getBundle()
    {
        return $this->bundle;
    }
}