<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Annotation\ORM as ORMExt;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource")
 * @ORMExt\Extendable(discriminatorColumn="discr")
 */
class Resource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    private $created;
     
    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updated;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User", inversedBy="resources")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\ResourceType", inversedBy="resources")
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    private $resourceType;
    
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getCreationDate()
    {
        return $this->created;
    }

    public function getModificationDate()
    {
        return $this->updated;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function setUser(User $user)
    {
       $this->user=$user;
    }
    
    public function getResourceType()
    {
        return $this->resourceType;
    }
    
    public function setResourceType(ResourceType $resourceType)
    {
       $this->resourceType=$resourceType;
    } 
    
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}