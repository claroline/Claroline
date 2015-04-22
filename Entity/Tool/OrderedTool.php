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
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

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
 *         )
 *     }
 * )
 *
 * @DoctrineAssert\UniqueEntity({"tool", "workspace", "type"})
 * @DoctrineAssert\UniqueEntity({"tool", "user", "type"})
 * @Gedmo\TranslationEntity(class="Claroline\CoreBundle\Entity\Translation\OrderedToolTranslation")
 */
class OrderedTool implements Translatable
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
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(length=255, nullable=true)
     */
    private $displayedName;

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

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;

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

    public function setDisplayedName($displayedName)
    {
        $this->displayedName = $displayedName;
    }

    public function getDisplayedName()
    {
        return $this->displayedName;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getTranslatableLocale()
    {
        return $this->locale;
    }
}
