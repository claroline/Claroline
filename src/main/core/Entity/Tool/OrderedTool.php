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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Order;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\AppBundle\Entity\Restriction\Hidden;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Tool\OrderedToolRepository")
 * @ORM\Table(
 *     name="claro_ordered_tool",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="ordered_tool_unique_tool_user_type",
 *             columns={"tool_id", "user_id"}
 *         ),
 *         @ORM\UniqueConstraint(
 *             name="ordered_tool_unique_tool_ws_type",
 *             columns={"tool_id", "workspace_id"}
 *         )
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"tool", "workspace"})
 * @DoctrineAssert\UniqueEntity({"tool", "user"})
 */
class OrderedTool
{
    use Id;
    use Uuid;
    // meta
    use Thumbnail;
    use Poster;
    use Order;
    use Hidden;

    /**
     * Display tool icon when the tool is rendered.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    private $showIcon = false;

    /**
     * Display in fullscreen when the tool is opened.
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $fullscreen = false;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     cascade={"persist", "merge"},
     *     inversedBy="orderedTools"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\Tool"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var Tool
     */
    private $tool;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\ToolRights",
     *     mappedBy="orderedTool"
     * )
     *
     * @var ToolRights[]|ArrayCollection
     */
    private $rights;

    public function __construct()
    {
        $this->refreshUuid();

        $this->rights = new ArrayCollection();
    }

    public function __toString()
    {
        if (!empty($this->workspace)) {
            return '['.$this->workspace->getName().'] '.$this->tool->getName();
        }

        return $this->tool->getName();
    }

    public function getShowIcon()
    {
        return $this->showIcon;
    }

    public function setShowIcon($showIcon)
    {
        $this->showIcon = $showIcon;
    }

    public function setFullscreen(bool $fullscreen)
    {
        $this->fullscreen = $fullscreen;
    }

    public function getFullscreen(): bool
    {
        return $this->fullscreen;
    }

    public function setWorkspace(Workspace $ws = null)
    {
        $this->workspace = $ws;
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setTool(Tool $tool)
    {
        $this->tool = $tool;
    }

    /**
     * @return Tool
     */
    public function getTool()
    {
        return $this->tool;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return ToolRights[]|ArrayCollection
     */
    public function getRights()
    {
        return $this->rights;
    }

    public function addRight(ToolRights $right)
    {
        if (!$this->rights->contains($right)) {
            $this->rights->add($right);
        }
    }

    public function removeRight(ToolRights $right)
    {
        if ($this->rights->contains($right)) {
            $this->rights->removeElement($right);
        }
    }
}
