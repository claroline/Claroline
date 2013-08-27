<?php

namespace Claroline\CoreBundle\Entity\Tool;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ToolRepository")
 * @ORM\Table(name="claro_tools")
 */
class Tool
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(unique=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="display_name", nullable=true)
     */
    protected $displayName;

    /**
     * @ORM\Column()
     */
    protected $class;

    /**
     * @ORM\Column(name="is_workspace_required", type="boolean")
     */
    protected $isWorkspaceRequired = false;

    /**
     * @ORM\Column(name="is_desktop_required", type="boolean")
     */
    protected $isDesktopRequired = false;

    /**
     * @ORM\Column(name="is_displayable_in_workspace", type="boolean")
     */
    protected $isDisplayableInWorkspace = true;

    /**
     * @ORM\Column(name="is_displayable_in_desktop", type="boolean")
     */
    protected $isDisplayableInDesktop = true;

    /**
     * @ORM\Column(type="boolean", name="is_exportable")
     */
    protected $isExportable = false;

    /**
     * @ORM\Column(type="boolean", name="is_configurable_in_workspace")
     */
    protected $isConfigurableInWorkspace = false;

    /**
     * @ORM\Column(type="boolean", name="is_configurable_in_desktop")
     */
    protected $isConfigurableInDesktop= false;

    /**
     * Unmapped var used for the tool configuration.
     *
     * @var boolean
     */
    private $isVisible = true;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $plugin;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     mappedBy="tool"
     * )
     */
    protected $orderedTools;

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    public function getDisplayName()
    {
        return $this->displayName;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setIsWorkspaceRequired($bool)
    {
        $this->isWorkspaceRequired = $bool;
    }

    public function isWorkspaceRequired()
    {
        return $this->isWorkspaceRequired;
    }

    public function setIsDesktopRequired($bool)
    {
        $this->isDesktopRequired = $bool;
    }

    public function isDesktopRequired()
    {
        return $this->isDesktopRequired;
    }

    public function setVisible($bool)
    {
        $this->isVisible = $bool;
    }

    public function isVisible()
    {
        return $this->isVisible;
    }

    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function setDisplayableInWorkspace($bool)
    {
        $this->isDisplayableInWorkspace = $bool;
    }

    public function isDisplayableInWorkspace()
    {
        return $this->isDisplayableInWorkspace;
    }

    public function setDisplayableInDesktop($bool)
    {
        $this->isDisplayableInDesktop = $bool;
    }

    public function isDisplayableInDesktop()
    {
        return $this->isDisplayableInDesktop;
    }

    public function setExportable($isExportable)
    {
        $this->isExportable = $isExportable;
    }

    public function isExportable()
    {
        return $this->isExportable;
    }

    public function getOrderedTools()
    {
        return $this->orderedTools;
    }

    public function setIsConfigurableInWorkspace($bool)
    {
        $this->isConfigurableInWorkspace = $bool;
    }

    public function isConfigurableInWorkspace()
    {
        return $this->isConfigurableInWorkspace;
    }

    public function setIsConfigurableInDesktop($bool)
    {
        $this->isConfigurableInDesktop = $bool;
    }

    public function isConfigurableInDesktop()
    {
        return $this->isConfigurableInDesktop;
    }
}
