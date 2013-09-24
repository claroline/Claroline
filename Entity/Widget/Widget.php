<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_widget")
 */
class Widget
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
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
     * @ORM\Column(unique=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="is_configurable", type="boolean")
     */
    protected $isConfigurable;

    /**
     * @ORM\Column()
     */
    protected $icon;

    /**
     * @ORM\Column(name="is_exportable", type="boolean")
     */
    protected $isExportable;

    /**
     * @ORM\Column(name="is_displayable_in_workspace", type="boolean")
     */
    protected $isDisplayableInWorkspace = true;
    
    /**
     * @ORM\Column(name="is_displayable_in_desktop", type="boolean")
     */
    protected $isDisplayableInDesktop = true;
    
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

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setExportable($isExportable)
    {
        $this->isExportable = $isExportable;
    }

    public function isExportable()
    {
        return $this->isExportable;
    }
    
    public function  isDisplayableInWorkspace()
    {
        return $this->isDisplayableInWorkspace;
    }
    
    public function setDisplayableInWorkspace($bool)
    {
        $this->isDisplayableInWorkspace = $bool;
    }
    
    public function  isDisplayableInDesktop()
    {
        return $this->isDisplayableInDesktop;
    }
    
    public function setDisplayableInDesktop($bool)
    {
        $this->isDisplayableInDesktop = $bool;
    }
}
