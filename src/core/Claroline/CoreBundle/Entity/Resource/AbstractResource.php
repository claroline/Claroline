<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"file" = "File", "directory" = "Directory"})
 */
abstract class AbstractResource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer") 
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank() 
     */
    protected $name;
    
    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceInstance", mappedBy="abstractResource")
     */
    protected $resourcesInstance;
    
    /**
     * @ORM\Column(type="integer", name="count_instance") 
     */
    protected $instanceAmount;
    
    public function __construct()
    {
        $this->resourcesInstance = new ArrayCollection();
        $this->instanceAmount = 0;
    }
        
    public function setId($id)
    {
        $this->id=$id;
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function addResourceInstance(ResourceInstance $resourceInstance)
    {
        $this->resourcesInstance->add($resourceInstance);
    }
    
    public function removeResourceInstance(ResourceInstance $resourceInstance)
    {
        $this->resourcesInstance->removeElement($resourceInstance);
    }
    
    public function addInstance()
    {
        $this->instanceAmount++;
    }
    
    public function removeInstance()
    {
        $this->instanceAmount--;
    }
    
    public function getInstanceAmount()
    {
        return $this->instanceAmount;
    }
          
}