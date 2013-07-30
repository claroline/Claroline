<?php

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\ResourceIconRepository")
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
     * @ORM\Column(type="string", name="icon_location", nullable=true, length=255)
     */
    protected $iconLocation;

    /**
     * @ORM\Column(type="string", name="mimeType", length=255)
     */
    protected $mimeType;

    /**
     * @ORM\Column(type="boolean", name="is_shortcut")
     */
    protected $isShortcut = false;

    /**
     * @ORM\Column(type="string", name="relative_url", nullable=true, length=255)
     * The url from the /web folder.
     */
    protected $relativeUrl;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceIcon")
     * @ORM\JoinColumn(name="shortcut_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
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

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
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
