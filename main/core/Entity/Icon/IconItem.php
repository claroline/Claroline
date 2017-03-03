<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 1/16/17
 */

namespace Claroline\CoreBundle\Entity\Icon;

use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Icon\IconItemRepository")
 * @ORM\Table(name="claro_icon_item")
 * Class IconItem
 */
class IconItem
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="mime_type", nullable=true)
     */
    private $mimeType;

    /**
     * @var string
     *
     * @ORM\Column(name="relative_url")
     */
    private $relativeUrl;

    /**
     * @var IconSet
     * @ORM\ManyToOne(targetEntity="IconSet", inversedBy="icons", fetch="LAZY")
     * @ORM\JoinColumn(name="icon_set_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $iconSet;

    /**
     * @var ResourceIcon
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceIcon", fetch="LAZY")
     * @ORM\JoinColumn(name="resource_icon_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $resourceIcon;

    /**
     * IconItem constructor.
     *
     * @param IconSet $iconSet
     * @param $relativeUrl
     * @param null $name
     * @param null $mimeType
     * @param null $class
     * @param bool $isShortcut
     * @param null $resourceIcon
     */
    public function __construct(
        IconSet $iconSet,
        $relativeUrl,
        $name = null,
        $mimeType = null,
        $class = null,
        $isShortcut = false,
        $resourceIcon = null
    ) {
        $this->iconSet = $iconSet;
        $this->relativeUrl = $relativeUrl;
        $this->name = $name;
        $this->mimeType = $mimeType;
        $this->class = $class;
        $this->isShortcut = $isShortcut;
        $this->resourceIcon = $resourceIcon;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return IconSet
     */
    public function getIconSet()
    {
        return $this->iconSet;
    }

    /**
     * @param IconSet $iconSet
     *
     * @return $this
     */
    public function setIconSet($iconSet)
    {
        $this->iconSet = $iconSet;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     *
     * @return $this
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * @return string
     */
    public function getRelativeUrl()
    {
        return $this->relativeUrl;
    }

    /**
     * @param string $relativeUrl
     *
     * @return $this
     */
    public function setRelativeUrl($relativeUrl)
    {
        $this->relativeUrl = $relativeUrl;

        return $this;
    }

    /**
     * @return ResourceIcon
     */
    public function getResourceIcon()
    {
        return $this->resourceIcon;
    }

    /**
     * @param ResourceIcon $resourceIcon
     *
     * @return $this
     */
    public function setResourceIcon($resourceIcon)
    {
        $this->resourceIcon = $resourceIcon;

        return $this;
    }
}
