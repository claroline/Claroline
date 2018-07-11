<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Home;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Poster;
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
    use Poster;
    use Uuid;

    const TYPE_WORKSPACE = 'workspace';
    const TYPE_DESKTOP = 'desktop';
    const TYPE_ADMIN_WORKSPACE = 'admin_workspace';
    const TYPE_ADMIN_DESKTOP = 'admin_desktop';

    /**
     * @ORM\Column(nullable=false)
     */
    protected $name;

    /**
     * @ORM\Column(nullable=false, type="text")
     */
    protected $longTitle;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $centerTitle = false;

    /**
     * @ORM\Column(nullable=false)
     */
    protected $type;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="CASCADE")
     */
    protected $workspace;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig",
     *     mappedBy="homeTab"
     * )
     */
    protected $widgetHomeTabConfigs;

    /**
     * @ORM\OneToMany(
     *     targetEntity="HomeTabConfig",
     *     mappedBy="homeTab"
     * )
     */
    protected $homeTabConfigs;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     * @ORM\JoinTable(name="claro_home_tab_roles")
     */
    protected $roles;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $icon;

    public function __construct()
    {
        $this->refreshUuid();
        $this->roles = new ArrayCollection();
        $this->widgetHomeTabConfigs = new ArrayCollection();
        $this->homeTabConfigs = new ArrayCollection();
        $this->centerTitle = false;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getWidgetHomeTabConfigs()
    {
        return $this->widgetHomeTabConfigs;
    }

    public function serializeForWidgetPicker()
    {
        $return = [
            'id' => $this->id,
            'name' => $this->name,
        ];

        return $return;
    }

    public function getRoles()
    {
        return $this->roles->toArray();
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

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function setLongTitle($title)
    {
        $this->longTitle = $title;
    }

    public function getLongTitle()
    {
        return $this->longTitle;
    }

    public function setCenterTitle($bool)
    {
        $this->centerTitle = $bool;
    }

    public function isCenterTitle()
    {
        return $this->centerTitle;
    }
}
