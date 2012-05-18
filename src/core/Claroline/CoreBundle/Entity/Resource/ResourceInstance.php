<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\AbstractResourceRepository")
 * @ORM\Table(name="claro_resource_instance")
 */

class ResourceInstance
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
    protected $created;
     
    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User", inversedBy="resourcesInstance")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType", inversedBy="resourcesInstance")
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    protected $resourceType;
    
    /**
     * @ORM\Column(name="resource_name", type="string", length=255)
     */
    protected $name;
    
    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceInstance", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceInstance", mappedBy="parent")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $children;
    
    /**
     * @ORM\Column(type="boolean")
     */
    protected $copy; 
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractWorkspace", inversedBy="resourcesInstance")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;
    
    public function __construct()
    {
        $this->workspaces = new ArrayCollection();
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
    
    public function setParent(AbstractResource $parent = null)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addChildren(AbstractResource $resource)
    {
        $this->children[] = $resource;
    }
          
    public function setCopy($copy)
    {
        $this->copy = $copy;
    }
    
    public function getCopy()
    {
        return $this->copy;
    }
    
    public function getRepositories()
    {
        return $this->repositories;
    }
    
    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }
    
    public function getWorkspace()
    {
        return $this->workspace;
    }
}