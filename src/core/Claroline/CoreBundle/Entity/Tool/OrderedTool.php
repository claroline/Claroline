<?php

namespace Claroline\CoreBundle\Entity\Tool;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\OrderedToolRepository")
 * @ORM\Table(
 *     name="claro_ordered_tool",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="tool", columns={"tool_id", "workspace_id"}),
 *         @ORM\UniqueConstraint(name="display", columns={"workspace_id", "display_order"}),
 *         @ORM\UniqueConstraint(name="workspace", columns={"workspace_id", "name"})
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"name", "workspace"})
 */
class OrderedTool
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace",
     *     cascade={"persist"}, inversedBy="orderedTools"
     * )
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\Tool",
     *     cascade={"persist"}, inversedBy="orderedTools"
     * )
     * @ORM\JoinColumn(name="tool_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $tool;

    /**
     * @ORM\Column(name="display_order", type="integer")
     */
    protected $order;

    /**
     * @Orm\Column(type="string")
     */
    protected $name;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     inversedBy="roles"
     * )
     * @ORM\JoinTable(
     *     name="claro_ordered_tool_role"
     * )
     */
    protected $roles;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}, inversedBy="orderedTools"
     * )
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setWorkspace(AbstractWorkspace $ws = null)
    {
        $this->workspace = $ws;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setTool(Tool $tool)
    {
        $this->tool = $tool;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function addRole(Role $role)
    {
        $this->roles->add($role);
    }

    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
