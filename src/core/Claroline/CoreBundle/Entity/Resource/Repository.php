<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_repository")
 */
class Repository
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource", 
     *      cascade={"persist"}
     * )
     * @ORM\JoinTable(name="claro_resource_repository",
     *      joinColumns={@ORM\JoinColumn(name="repository_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")}
     * )
     */
    protected $resources;
    
    public function __construct()
    {
        $this->resources = new ArrayCollection();
    }
    

    public function getId()
    {
        return $this->id;
    }
    
    public function getResources()
    {
        return $this->resources;
    }
    
    public function addResource(AbstractResource $resource)
    {
        $this->resources->add($resource);
        $resource->getRepositories()->add($this);
    }
    
    public function removeResource(AbstractResource $resource)
    {
        $this->resources->removeElement($resource);
    }
}