<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\HomeBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Order;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Restriction\AccessCode;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\AppBundle\Entity\Restriction\Hidden;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_home_tab")
 */
class HomeTab
{
    use Id;
    use Uuid;
    use Order;
    use Poster;
    // restrictions
    use Hidden;
    use AccessibleFrom;
    use AccessibleUntil;
    use AccessCode;

    const TYPE_WORKSPACE = 'workspace';
    const TYPE_DESKTOP = 'desktop';
    const TYPE_ADMIN_DESKTOP = 'administration';
    const TYPE_HOME = 'home';
    const TYPE_ADMIN = 'admin';

    /**
     * @ORM\Column(nullable=false)
     *
     * @var string
     */
    private $context;

    /**
     * @ORM\Column(nullable=false)
     *
     * @var string
     */
    private $type;

    /**
     * The class that holds the tab custom configuration if any.
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $class = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(nullable=false, type="text")
     */
    private $longTitle = '';

    /**
     * @ORM\Column(type="boolean")
     */
    private $centerTitle = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=1})
     */
    private $showTitle = true;

    /**
     * @ORM\Column(nullable=true)
     */
    private $icon;

    /**
     * The color of the tab.
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $color = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="CASCADE")
     *
     * @var User
     */
    private $user = null;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace")
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="CASCADE")
     *
     * @var Workspace
     */
    private $workspace = null;

    /**
     * Parent tab.
     *
     * @var HomeTab
     *
     * @ORM\ManyToOne(targetEntity="Claroline\HomeBundle\Entity\HomeTab", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent = null;

    /**
     * Children tabs.
     *
     * @var ArrayCollection|HomeTab[]
     *
     * @ORM\OneToMany(targetEntity="Claroline\HomeBundle\Entity\HomeTab", mappedBy="parent", cascade={"persist", "remove"})
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private $children;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinTable(name="claro_home_tab_roles")
     *
     * @var Role[]
     */
    private $roles;

    public function __construct()
    {
        $this->refreshUuid();

        $this->children = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function setContext(string $context)
    {
        $this->context = $context;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class = null)
    {
        $this->class = $class;
    }

    /**
     * Get color.
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set color.
     *
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getLongTitle()
    {
        return $this->longTitle;
    }

    public function setLongTitle($longTitle)
    {
        $this->longTitle = $longTitle;
    }

    public function isCenterTitle()
    {
        return $this->centerTitle;
    }

    public function setCenterTitle($centerTitle)
    {
        $this->centerTitle = $centerTitle;
    }

    public function getShowTitle(): bool
    {
        return $this->showTitle;
    }

    public function setShowTitle(bool $showTitle)
    {
        $this->showTitle = $showTitle;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    /**
     * Set parent.
     */
    public function setParent(HomeTab $parent = null)
    {
        if ($parent !== $this->parent) {
            $this->parent = $parent;

            if (null !== $parent) {
                $parent->addChild($this);
            }
        }
    }

    /**
     * Get parent.
     */
    public function getParent(): ?HomeTab
    {
        return $this->parent;
    }

    /**
     * Get children of the tab.
     *
     * @return ArrayCollection|HomeTab[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add new child to the tab.
     */
    public function addChild(HomeTab $homeTab)
    {
        if (!$this->children->contains($homeTab)) {
            $this->children->add($homeTab);
            $homeTab->setParent($this);
        }
    }

    /**
     * Remove a tab from children.
     */
    public function removeChild(HomeTab $homeTab)
    {
        if ($this->children->contains($homeTab)) {
            $this->children->removeElement($homeTab);
            $homeTab->setParent(null);
        }

        return $this;
    }

    /**
     * @return Role[]|ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    public function emptyRoles()
    {
        $this->roles->clear();
    }
}
