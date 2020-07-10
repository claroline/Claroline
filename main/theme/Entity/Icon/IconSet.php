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

namespace Claroline\ThemeBundle\Entity\Icon;

use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\ThemeBundle\Library\Icon\IconSetTypeEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_icon_set")
 * Class IconSet
 */
class IconSet
{
    use Uuid;

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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="IconItem", mappedBy="iconSet")
     */
    private $icons;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default" = 0})
     */
    private $editable = false;

    public function __construct()
    {
        $this->refreshUuid();

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
        if (null === $type) {
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
     * @return string
     */
    public function getCname()
    {
        return $this->cname;
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->editable;
    }

    /**
     * @param bool $editable
     */
    public function setEditable($editable)
    {
        $this->editable = $editable;
    }
}
