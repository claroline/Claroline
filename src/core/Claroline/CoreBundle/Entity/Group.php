<?php

namespace Claroline\CoreBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\GroupRepository")
 * @ORM\Table(name="claro_group")
 * @DoctrineAssert\UniqueEntity("name")
 */
class Group extends AbstractRoleSubject
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length="50")
     * @Assert\NotBlank()
     */
    protected $name;
    
    /**
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\User", 
     *      cascade={"persist"}
     * )
     * @ORM\JoinTable(name="claro_user_group",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     */
    protected $users;
    
    /**
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\Role", 
     *      cascade={"persist"}
     * )
     * @ORM\JoinTable(name="claro_group_role",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $roles;
    
    /**
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\CoreBundle\Entity\WorkspaceRole", 
     *      inversedBy="groups"
     * )
     * @ORM\JoinTable(name="claro_group_role",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     */
    protected $workspaceRoles;
    
    public function __construct()
    {
        parent::__construct();
        $this->workspaceRoles = new ArrayCollection();
        $this->users = new ArrayCollection();
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
    
    public function addUser(User $user)
    {
        $this->users->add($user);
        $user->getGroups()->add($this);
    }
    
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
        $user->getGroups()->removeElement($this);
    }
    
    public function getUsers()
    {
        return $this->users;      
    }

    /**
     * Returns the group's workspace roles as an ArrayCollection of WorkspaceRole objects.
     * 
     * @return ArrayCollection[WorkspaceRole]
     */
    public function getWorkspaceRoleCollection()
    {
        return $this->workspaceRoles;
    }
}