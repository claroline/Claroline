<?php

namespace Claroline\CoreBundle\Entity\Workspace;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\WorkspaceRole;
use Claroline\CoreBundle\Entity\ToolInstance;
use Claroline\CoreBundle\Exception\ClarolineException;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRepository")
 * @ORM\Table(name="claro_workspace")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({
 *      "Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace" = "Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace",
 *      "Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace" = "Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace"
 * })
 */
abstract class AbstractWorkspace
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length="255")
     * @Assert\NotBlank()
     */
    private $name;
    
    /**
     * @ORM\Column(name="is_public", type="boolean")
     */
    protected $isPublic = true;
    
    /**
     * @ORM\OneToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\WorkspaceRole", 
     *  mappedBy="workspace",
     *  cascade={"persist"}
     * )
     */
    private $roles;
    
    /**
     * @ORM\OneToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\ToolInstance", 
     *  mappedBy="hostWorkspace"
     * )
     */
    private $tools;
    
    /**
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Resource\AbstractResource", 
     *      cascade={"persist"}
     * )
     * @ORM\JoinTable(name="claro_workspace_resource",
     *      joinColumns={@ORM\JoinColumn(name="workspace_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")}
     * )
     */
    protected $resources;
    
    private static $visitorPrefix = 'ROLE_WS_VISITOR';
    private static $collaboratorPrefix = 'ROLE_WS_COLLABORATOR';
    private static $managerPrefix = 'ROLE_WS_MANAGER';
    private static $customPrefix = 'ROLE_WS_CUSTOM';
    
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->tools = new ArrayCollection();
        $this->resources = new ArrayCollection();
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
    
    abstract function setPublic($isPublic);
    
    public function isPublic()
    {
        return $this->isPublic;
    }
    
    /**
     * Creates the three workspace base roles (visitor, collaborator, manager)
     * and attaches them to the workspace instance. As the workspace role names 
     * require the workspace to have a valid identifier, this method can't be used
     * on a workspace instance that has never been flushed.
     * 
     * @throw ClarolineException if the workspace has no valid id
     */
    public function initBaseRoles()
    {
        $this->checkIdCondition();
        
        foreach ($this->roles as $storedRole)
        {
            if (self::isBaseRole($storedRole->getName()))
            {
                throw new ClarolineException('Base workspace roles are already set.');
            }
        }

        $this->doAddBaseRole(self::$visitorPrefix);
        $this->doAddBaseRole(self::$collaboratorPrefix);
        $this->doAddBaseRole(self::$managerPrefix);
    }

    public function getVisitorRole()
    {
        return $this->doGetBaseRole(self::$visitorPrefix);
    }
    
    public function getCollaboratorRole()
    {
        return $this->doGetBaseRole(self::$collaboratorPrefix);
    }
    
    public function getManagerRole()
    {
        return $this->doGetBaseRole(self::$managerPrefix);
    }
      
    /**
     * Returns the custom roles attached to the workspace instance. Note that
     * the returned collection is not the actual entity's role collection, so
     * using add/remove operations on it won't affect the entity's realtionships
     * (use addCustomRole and removeCustomRole to achieve that goal).
     * 
     * @return ArrayCollection[WorkspaceRole]
     */
    public function getCustomRoles()
    {
        $customRoles = new ArrayCollection();
        
        foreach ($this->roles as $role)
        {
            if (self::isCustomRole($role->getName()))
            {
                $customRoles[] = $role;
            }
        }
        
        return $customRoles;
    }
    
    /**
     * Adds a custom role to the workspace's role collection. If the role doesn't have
     * a name or if the workspace doesn't have a valid identifier (i.e. hasn't been 
     * flushed yet), an exception will be thrown.
     * 
     * @param WorkspaceRole $role
     * @throw ClarolineException if the workspace has no id or if the role has no name
     */
    public function addCustomRole(WorkspaceRole $role)
    {
        $this->checkIdCondition();
        
        if ($this->roles->contains($role))
        {
            return;
        }
        
        $workspace = $role->getWorkspace();
        
        if (! $workspace instanceof AbstractWorkspace)
        {
            $role->setWorkspace($this);
        }
        else
        {
            if ($workspace !== $this)
            {
                throw new ClarolineException(
                    'Workspace roles are bound to only one workspace and cannot '
                    . 'be associated with another workspace.'
                );
            }
        }
        
        $roleName = $role->getName();
        
        if (! is_string($roleName) || 0 == strlen($roleName))
        {
            throw new ClarolineException('Workspace role must have a valid name.');
        }
        
        $newRoleName = self::$customPrefix . "_{$this->getId()}_{$roleName}";
        $role->setName($newRoleName);      
        $this->roles->add($role);
    }
    
    public function removeCustomRole(WorkspaceRole $role)
    {
        if (0 === strpos($role->getName(), self::$customPrefix . "_{$this->getId()}_"))
        {
            $this->roles->removeElement($role);
        }
    }
    
    public static function isBaseRole($roleName)
    {
        if (0 === strpos($roleName, self::$visitorPrefix)
            || 0 === strpos($roleName, self::$collaboratorPrefix)
            || 0 === strpos($roleName, self::$managerPrefix))
        {
            return true;
        }
        
        return false;
    }
    
    public static function isCustomRole($roleName)
    {
        if (0 === strpos($roleName, self::$customPrefix))
        {
            return true;
        }
        
        return false;
    }
   
    public function addToolInstance(ToolInstance $toolInstance)
    {
        $this->tools->add($toolInstance);  
    }
    
    public function removeToolInstance(ToolInstance $toolInstance)
    {
        $this->tools->removeElement($toolInstance);  
    }
    
    public function getTools()
    {
        return $this->tools;
    }
    
    private function checkIdCondition()
    {
        if (null === $this->id)
        {
            throw new ClarolineException(
                'Workspace must be flushed and have a valid id '
                . 'before associating roles to it.'
            );
        }
    }
    
    private function doAddBaseRole($prefix)
    {
        $baseRole = new WorkspaceRole();
        $baseRole->setWorkspace($this);
        $baseRole->setName("{$prefix}_{$this->getId()}");
        $this->roles->add($baseRole);
    }
 
    private function doGetBaseRole($prefix)
    {
        foreach ($this->roles as $role)
        {
            if (0 === strpos($role->getName(), $prefix))
            {
                return $role;
            }
        }
    }
    
    public function getWorkspaceRoles()
    {
        return $this->roles;
    }
    
    public function getResources()
    {
        return $this->resources;
    }
    
    public function addResource(AbstractResource $resource)
    {
        $this->resources->add($resource);
        //$resource->getWorkspace()->add($this);
    }
    
    public function removeResource(AbstractResource $resource)
    {
        $this->resources->removeElement($resource);
        $resource->getWorkspace()->removeElement($this);
    }
}