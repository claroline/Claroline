<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\AbstractResourceRepository")
 * @ORM\Table(name="claro_resource")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"file" = "File", "directory" = "Directory"})
 */
/*abstract*/ class AbstractResource
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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User", inversedBy="resources")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceType", inversedBy="resources")
     * @ORM\JoinColumn(name="resource_type_id", referencedColumnName="id")
     */
    protected $resourceType;
    
    /**
     * @ORM\Column(type="string", length=255)
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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource", mappedBy="parent")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $children;
    
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace", inversedBy="resources")
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    protected $workspace;
    
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
    
    public function getWorkspace()
    {
        return $this->workspace;
    }
    
    public function setWorkspace($workspace)
    {
        $this->workspace=$workspace;
    }
}