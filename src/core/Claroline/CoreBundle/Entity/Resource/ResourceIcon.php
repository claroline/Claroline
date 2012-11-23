<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Resource\IconType;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_resource_icon")
 */
class ResourceIcon
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="icon_location")
     */
    protected $iconLocation;

    /**
     * @ORM\Column(type="string", name="type")
     */
    protected $type;

    /**
     * @ORM\Column(type="boolean", name="is_shortcut")
     */
    protected $isShortcut;

    /**
     * @ORM\Column(type="string", name="relative_url")
     */
    protected $relativeUrl;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\IconType")
     * @ORM\JoinColumn(name="icon_type_id", referencedColumnName="id")
     */
    protected $iconType;

    /**
     * @ORM\OneToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceIcon")
     * @ORM\JoinColumn(name="shortcut_id", referencedColumnName="id")
     */
    protected $shortcutIcon;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->abstractResources = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIconLocation()
    {
        return $this->iconLocation;
    }

    public function setIconLocation($iconLocation)
    {
        $this->iconLocation = $iconLocation;
    }

    public function setIconType(IconType $iconType)
    {
        $this->iconType = $iconType;
    }

    public function getIconType()
    {
        return $this->iconType;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function isShortcut()
    {
        return $this->isShortcut;
    }

    public function setShortcut($boolean)
    {
        $this->isShortcut = $boolean;
    }

    public function getShortcutIcon()
    {
        return $this->shortcutIcon;
    }

    public function setShortcutIcon(ResourceIcon $shortcutIcon)
    {
        $this->shortcutIcon = $shortcutIcon;
    }

    public function setRelativeUrl($url)
    {
        $this->relativeUrl = $url;
    }

    public function getRelativeUrl()
    {
        return $this->relativeUrl;
    }
}