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
     * @ORM\Column(name="icon_location", nullable=true)
     */
    protected $iconLocation;

    /**
     * @ORM\Column()
     */
    protected $mimeType;

    /**
     * @ORM\Column(name="is_shortcut", type="boolean")
     */
    protected $isShortcut = false;

    /**
     * @ORM\Column(name="relative_url", nullable=true)
     *
     * The url from the /web folder.
     */
    protected $relativeUrl;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceIcon")
     * @ORM\JoinColumn(name="shortcut_id", onDelete="SET NULL")
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
