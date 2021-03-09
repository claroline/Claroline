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
use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_home_tab_config")
 */
class HomeTabConfig
{
    use Id;

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
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\HomeBundle\Entity\HomeTab",
     *     cascade={"persist", "remove"},
     *     inversedBy="homeTabConfigs"
     * )
     * @ORM\JoinColumn(name="home_tab_id", nullable=false, onDelete="CASCADE")
     *
     * @var HomeTab
     */
    private $homeTab;

    /**
     * @ORM\Column(type="boolean", name="is_visible")
     *
     * @var bool
     */
    private $visible = true;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @var array
     */
    private $details;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinTable(name="claro_home_tab_roles")
     *
     * @var Role[]
     */
    private $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
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

    public function getHomeTab()
    {
        return $this->homeTab;
    }

    public function setHomeTab(HomeTab $homeTab)
    {
        $this->homeTab = $homeTab;
        $homeTab->addHomeTabConfig($this);
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
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
}
