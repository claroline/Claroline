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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Icon\IconSetRepository")
 * @ORM\Table(name="claro_icon_set")
 * Class IconSet
 */
class IconSet
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
     * @ORM\Column()
     */
    private $name;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"}, unique=true, updatable=false, separator="_")
     * @ORM\Column(unique=true)
     */
    private $cname;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_default", type="boolean")
     */
    private $default = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $active = false;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $type;

    /**
     * @var string Relative path to icon that is used as a stamp to create shortcuts for resource Icon Set
     * @ORM\Column(name="resource_stamp_icon", nullable=true)
     */
    private $resourceStampIcon = null;

    /**
     * @var string the Sprite file containing all icons in the icon set
     * @ORM\Column(name="icon_sprite", nullable=true)
     */
    private $iconSprite = null;

    /**
     * @var string the CSS file corresponding to the Icon Set's sprite file
     * @ORM\Column(name="icon_sprite_css", nullable=true)
     */
    private $iconSpriteCSS = null;

    /**
     * @Assert\File(
     *     mimeTypes = {"application/zip"}
     * )
     */
    private $iconsZipfile;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="IconItem", mappedBy="iconSet")
     * @JMS\Groups({"details"})
     */
    private $icons;

    public function __construct()
    {
        $this->icons = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param bool $default
     *
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        if ($type === null) {
            $this->$type = $type;
        } else {
            $iconSetType = new IconSetTypeEnum($type);
            $this->type = $iconSetType->getValue();
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getIcons()
    {
        return $this->icons;
    }

    /**
     * @param array $icons
     *
     * @return $this
     */
    public function setIcons($icons)
    {
        $this->icons = $icons;

        return $this;
    }

    public function addIcon(IconItem $icon)
    {
        $this->icons->add($icon);
    }

    /**
     * @return mixed
     */
    public function getIconsZipfile()
    {
        return $this->iconsZipfile;
    }

    /**
     * @param mixed $iconsZipfile
     *
     * @return $this
     */
    public function setIconsZipfile($iconsZipfile)
    {
        $this->iconsZipfile = $iconsZipfile;

        return $this;
    }

    /**
     * @return string
     */
    public function getCname()
    {
        return $this->cname;
    }

    /**
     * @return string
     */
    public function getResourceStampIcon()
    {
        return $this->resourceStampIcon;
    }

    /**
     * @param string $resourceStampIcon
     *
     * @return $this
     */
    public function setResourceStampIcon($resourceStampIcon)
    {
        $this->resourceStampIcon = $resourceStampIcon;

        return $this;
    }

    /**
     * @return string
     */
    public function getIconSprite()
    {
        return $this->iconSprite;
    }

    /**
     * @param string $iconSprite
     *
     * @return $this
     */
    public function setIconSprite($iconSprite)
    {
        $this->iconSprite = $iconSprite;

        return $this;
    }

    /**
     * @return string
     */
    public function getIconSpriteCSS()
    {
        return $this->iconSpriteCSS;
    }

    /**
     * @param string $iconSpriteCSS
     *
     * @return $this
     */
    public function setIconSpriteCSS($iconSpriteCSS)
    {
        $this->iconSpriteCSS = $iconSpriteCSS;

        return $this;
    }
}
