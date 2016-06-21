<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Tool;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\OrderedToolRepository")
 * @ORM\Table(
 *     name="claro_ordered_tool",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="ordered_tool_unique_tool_user_type",
 *             columns={"tool_id", "user_id", "ordered_tool_type"}
 *         ),
 *         @ORM\UniqueConstraint(
 *             name="ordered_tool_unique_tool_ws_type",
 *             columns={"tool_id", "workspace_id", "ordered_tool_type"}
 *         ),
 *         @ORM\UniqueConstraint(
 *             name="ordered_tool_unique_name_by_workspace",
 *             columns={"workspace_id", "name"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"name", "workspace"})
 * @DoctrineAssert\UniqueEntity({"tool", "workspace", "type"})
 * @DoctrineAssert\UniqueEntity({"tool", "user", "type"})
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
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     cascade={"persist", "merge"},
     *     inversedBy="orderedTools"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\Tool",
     *     cascade={"persist", "merge"},
     *     inversedBy="orderedTools"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $tool;

    /**
     * @ORM\Column(name="display_order", type="integer")
     */
    protected $order;

    /**
     * @ORM\Column()
     */
    protected $name;

    /**
     * @ORM\Column(name="is_visible_in_desktop", type="boolean")
     */
    protected $isVisibleInDesktop = false;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"},
     *     inversedBy="orderedTools"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\ToolRights",
     *     mappedBy="orderedTool"
     * )
     */
    protected $rights;

    /**
     * @ORM\Column(name="ordered_tool_type", type="integer")
     */
    protected $type = 0;

    /**
     * @ORM\Column(name="is_locked", type="boolean")
     */
    protected $locked = false;

    public function __construct()
    {
        $this->rights = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setWorkspace(Workspace $ws = null)
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

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function isVisibleInDesktop()
    {
        return $this->isVisibleInDesktop;
    }

    public function setVisibleInDesktop($isVisible)
    {
        $this->isVisibleInDesktop = $isVisible;
    }

    public function getRights()
    {
        return $this->rights;
    }

    public function addRight(ToolRights $right)
    {
        $this->rights->add($right);
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function isLocked()
    {
        return $this->locked;
    }

    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    public function __toString()
    {
        return is_null($this->workspace) ?
            $this->name :
            '['.$this->workspace->getName().'] '.$this->name;
    }
}
