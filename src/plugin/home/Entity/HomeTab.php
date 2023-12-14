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

use Claroline\AppBundle\Entity\Display\Color;
use Claroline\AppBundle\Entity\Display\Hidden;
use Claroline\AppBundle\Entity\Display\Icon;
use Claroline\AppBundle\Entity\Display\Order;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\HasContext;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Restriction\AccessCode;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="claro_home_tab")
 */
class HomeTab
{
    use Id;
    use Uuid;
    // display
    use Order;
    use Poster;
    use Color;
    use Icon;
    use Hidden;
    // restrictions
    use AccessibleFrom;
    use AccessibleUntil;
    use AccessCode;
    use HasContext;

    /**
     * The type of the tab (e.g. Widgets, Url).
     *
     * @ORM\Column(nullable=false)
     */
    private string $type;

    /**
     * The class that holds the tab custom configuration if any.
     *
     * @ORM\Column(nullable=true)
     */
    private ?string $class = null;

    /**
     * @ORM\Column(nullable=true)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(nullable=false, type="text")
     */
    private string $longTitle = '';

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $centerTitle = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=1})
     */
    private bool $showTitle = true;

    /**
     * Parent tab.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\HomeBundle\Entity\HomeTab", inversedBy="children")
     *
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?HomeTab $parent = null;

    /**
     * Children tabs.
     *
     * @var ArrayCollection|HomeTab[]
     *
     * @ORM\OneToMany(targetEntity="Claroline\HomeBundle\Entity\HomeTab", mappedBy="parent", cascade={"persist", "remove"})
     *
     * @ORM\OrderBy({"order" = "ASC"})
     */
    private $children;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Role")
     *
     * @ORM\JoinTable(name="claro_home_tab_roles")
     *
     * @var ArrayCollection|Role[]
     */
    private $roles;

    public function __construct()
    {
        $this->refreshUuid();

        $this->children = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class = null): void
    {
        $this->class = $class;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name = null): void
    {
        $this->name = $name;
    }

    public function getLongTitle(): string
    {
        return $this->longTitle;
    }

    public function setLongTitle($longTitle): void
    {
        $this->longTitle = $longTitle;
    }

    public function isCenterTitle()
    {
        return $this->centerTitle;
    }

    public function setCenterTitle($centerTitle): void
    {
        $this->centerTitle = $centerTitle;
    }

    public function getShowTitle(): bool
    {
        return $this->showTitle;
    }

    public function setShowTitle(bool $showTitle): void
    {
        $this->showTitle = $showTitle;
    }

    /**
     * Set parent.
     */
    public function setParent(HomeTab $parent = null): void
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
    public function addChild(HomeTab $homeTab): void
    {
        if (!$this->children->contains($homeTab)) {
            $this->children->add($homeTab);
            $homeTab->setParent($this);
        }
    }

    /**
     * Remove a tab from children.
     */
    public function removeChild(HomeTab $homeTab): void
    {
        if ($this->children->contains($homeTab)) {
            $this->children->removeElement($homeTab);
            $homeTab->setParent(null);
        }
    }

    /**
     * @return Role[]|ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    public function addRole(Role $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function removeRole(Role $role): void
    {
        $this->roles->removeElement($role);
    }
}
