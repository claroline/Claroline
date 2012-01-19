<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Tool;
use Claroline\CoreBundle\Entity\ToolInstance;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRepository")
 * @ORM\Table(name="claro_workspace")
 */
class Workspace
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length="255")
     * @Assert\NotBlank(message="workspace.name_not_blank")
     */
    protected $name;

    /**
     * @ORM\ManyToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\User", 
     *  inversedBy="workspaces"
     * )
     * @ORM\JoinTable(name="claro_workspace_user")
     */
    protected $users;
    
    /**
     * @ORM\OneToMany(
     *  targetEntity="Claroline\CoreBundle\Entity\ToolInstance", 
     *  mappedBy="hostWorkspace"
     * )
     */
    protected $tools;

    public function __construct()
    {
        $this->tools = new ArrayCollection();
        $this->users = new ArrayCollection();
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
    
    public function addUser(User $user)
    {
        $this->users->add($user);
        $user->getWorkspaceCollection()->add($this);
    }
    
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
        $user->getWorkspaceCollection()->removeElement($this);
    }
    
    public function getUsers()
    {
        return $this->users->toArray();
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