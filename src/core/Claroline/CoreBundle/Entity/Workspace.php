<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\WorkspaceRole;
use Claroline\CoreBundle\Entity\Tool;
use Claroline\CoreBundle\Entity\ToolInstance;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_workspace")
 */
class Workspace
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length="255")
     * @Assert\NotBlank(message="workspace.name_not_blank")
     */
    private $name;
    
    /**
     * @ORM\OneToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\WorkspaceRole", 
     *  mappedBy="workspace"
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

    public function __construct()
    {
        $this->tools = new ArrayCollection();
        $this->roles = new ArrayCollection();
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
    
    public function getRoles()
    {
        return $this->roles;
    }

    public function addRole(WorkspaceRole $role)
    {
        $this->roles->add($role);
        $role->setWorkspace($this);
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
        return $this->tools->toArray();
    }

    public function addTool(Tool $tool)
    {
        $this->tools->add($tool);
    } 
}