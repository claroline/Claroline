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

use Claroline\CoreBundle\Entity\Plugin;
use Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ToolRepository")
 * @ORM\Table(name="claro_tools")
 * @Gedmo\TranslationEntity(class="Claroline\CoreBundle\Entity\Translation\ToolTranslation")
 */
class Tool implements Translatable
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
     * @var string
     * @Gedmo\Translatable
     * @ORM\Column(length=255, nullable=true)
     */
    private $displayedName;

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
    protected $isConfigurableInDesktop = false;

    /**
     * @ORM\Column(type="boolean", name="is_locked_for_admin")
     */
    protected $isLockedForAdmin = false;

    /**
     * @ORM\Column(type="boolean", name="is_anonymous_excluded")
     */
    protected $isAnonymousExcluded = true;

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

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\ToolMaskDecoder",
     *     mappedBy="tool",
     *     cascade={"persist"}
     * )
     */
    protected $maskDecoders;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\PwsToolConfig",
     *     mappedBy="tool"
     * )
     */
    protected $pwsToolConfig;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;


    public function __construct()
    {
        $this->maskDecoders  = new ArrayCollection();
        $this->pwsToolConfig = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setIsWorkspaceRequired($bool)
    {
        $this->isWorkspaceRequired = $bool;

        return $this;
    }

    public function isWorkspaceRequired()
    {
        return $this->isWorkspaceRequired;
    }

    public function setIsDesktopRequired($bool)
    {
        $this->isDesktopRequired = $bool;

        return $this;
    }

    public function isDesktopRequired()
    {
        return $this->isDesktopRequired;
    }

    public function setVisible($bool)
    {
        $this->isVisible = $bool;

        return $this;
    }

    public function isVisible()
    {
        return $this->isVisible;
    }

    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;

        return $this;
    }

    public function getPlugin()
    {
        return $this->plugin;
    }

    public function setDisplayableInWorkspace($bool)
    {
        $this->isDisplayableInWorkspace = $bool;

        return $this;
    }

    public function isDisplayableInWorkspace()
    {
        return $this->isDisplayableInWorkspace;
    }

    public function setDisplayableInDesktop($bool)
    {
        $this->isDisplayableInDesktop = $bool;

        return $this;
    }

    public function isDisplayableInDesktop()
    {
        return $this->isDisplayableInDesktop;
    }

    public function setExportable($isExportable)
    {
        $this->isExportable = $isExportable;

        return $this;
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

        return $this;
    }

    public function isConfigurableInWorkspace()
    {
        return $this->isConfigurableInWorkspace;
    }

    public function setIsConfigurableInDesktop($bool)
    {
        $this->isConfigurableInDesktop = $bool;

        return $this;
    }

    public function isConfigurableInDesktop()
    {
        return $this->isConfigurableInDesktop;
    }

    public function setIsLockedForAdmin($isLockedForAdmin)
    {
        $this->isLockedForAdmin = $isLockedForAdmin;

        return $this;
    }

    public function isLockedForAdmin()
    {
        return $this->isLockedForAdmin;
    }

    public function setIsAnonymousExcluded($isAnonymousExcluded)
    {
        $this->isAnonymousExcluded = $isAnonymousExcluded;

        return $this;
    }

    public function isAnonymousExcluded()
    {
        return $this->isAnonymousExcluded;
    }

    public function addMaskDecoder(ToolMaskDecoder $maskDecoder)
    {
        $this->maskDecoders->add($maskDecoder);
    }

    public function getMaskDecoders()
    {
        return $this->maskDecoders;
    }

    public function addPwsToolConfig(PwsToolConfig $tr)
    {
        $this->pwsToolConfig->add($tr);
    }

    public function getPwsToolConfig()
    {
        return $this->pwsToolConfig;
    }

    public function setDisplayedName($name)
    {
        $this->displayedName = $name;
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
