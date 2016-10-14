<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Widget;

use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WidgetRepository")
 * @ORM\Table(
 *      name="claro_widget",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="widget_plugin_unique",columns={"name", "plugin_id"})}
 * )
 */
class Widget
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_widget"})
     * @SerializedName("id")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Plugin",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $plugin;

    /**
     * @ORM\Column()
     * @Groups({"api_widget"})
     * @SerializedName("name")
     */
    protected $name;

    /**
     * @ORM\Column(name="is_configurable", type="boolean")
     * @Groups({"api_widget"})
     * @SerializedName("isConfigurable")
     */
    protected $isConfigurable;

    /**
     * @ORM\Column(name="is_exportable", type="boolean")
     * @Groups({"api_widget"})
     * @SerializedName("isExportable")
     */
    protected $isExportable;

    /**
     * @ORM\Column(name="is_displayable_in_workspace", type="boolean")
     * @Groups({"api_widget"})
     * @SerializedName("isDisplayableInWorkspace")
     */
    protected $isDisplayableInWorkspace = true;

    /**
     * @ORM\Column(name="is_displayable_in_desktop", type="boolean")
     * @Groups({"api_widget"})
     * @SerializedName("isDisplayableInDesktop")
     */
    protected $isDisplayableInDesktop = true;

    /**
     * @ORM\Column(name="default_width", type="integer", options={"default":4})
     * @Groups({"api_widget"})
     * @SerializedName("defaultWidth")
     */
    protected $defaultWidth = 4;

    /**
     * @ORM\Column(name="default_height", type="integer", options={"default":3})
     * @Groups({"api_widget"})
     * @SerializedName("defaultHeight")
     */
    protected $defaultHeight = 3;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     * @ORM\JoinTable(name="claro_widget_roles")
     */
    protected $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function isConfigurable()
    {
        return $this->isConfigurable;
    }

    public function setConfigurable($bool)
    {
        $this->isConfigurable = $bool;
    }

    public function setExportable($isExportable)
    {
        $this->isExportable = $isExportable;
    }

    public function isExportable()
    {
        return $this->isExportable;
    }

    public function isDisplayableInWorkspace()
    {
        return $this->isDisplayableInWorkspace;
    }

    public function setDisplayableInWorkspace($bool)
    {
        $this->isDisplayableInWorkspace = $bool;
    }

    public function setIsDisplayableInWorkspace($bool)
    {
        $this->isDisplayableInWorkspace = $bool;
    }

    public function isDisplayableInDesktop()
    {
        return $this->isDisplayableInDesktop;
    }

    public function setDisplayableInDesktop($bool)
    {
        $this->isDisplayableInDesktop = $bool;
    }

    public function setIsDisplayableInDesktop($bool)
    {
        $this->isDisplayableInDesktop = $bool;
    }

    public function getDefaultWidth()
    {
        return $this->defaultWidth;
    }

    public function setDefaultWidth($defaultWidth)
    {
        $this->defaultWidth = $defaultWidth;
    }

    public function getDefaultHeight()
    {
        return $this->defaultHeight;
    }

    public function setDefaultHeight($defaultHeight)
    {
        $this->defaultHeight = $defaultHeight;
    }

    public function __toString()
    {
        return $this->name;
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
}
