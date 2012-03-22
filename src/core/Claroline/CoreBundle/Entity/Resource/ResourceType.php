<?php
namespace Claroline\CoreBundle\Entity\Resource;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @ORM\Column(type="string", length=255)
     */
    private $service;    
    
    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource", mappedBy="resource_type")
     */
    private $resources;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $isNavigable;
    
    /**
     * @ORM\Column(type="boolean")
     */
    private $isListable;
        
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
         
    public function setBundle($bundle)
    {
        $this->bundle=$bundle;
    }
    
    public function getBundle()
    {
        return $this->bundle;
    }
    
    public function setService($service)
    {
        $this->service=$service;
    }
    
    public function getService()
    {
        return $this->service;
    }
    
    public function setNavigable($isNavigable)
    {
        $this->isNavigable=$isNavigable;
    }
    
    public function getNavigable()
    {
        return $this->isNavigable;
    }
    
    public function setListable($isListable)
    {
        $this->isListable=$isListable;
    }
    
    public function getListable()
    {
        return $this->isListable;
    }
}