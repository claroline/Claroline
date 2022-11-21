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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Name;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_icon_set")
 */
class IconSet
{
    use Id;
    use Uuid;
    use Name;

    const RESOURCE_ICON_SET = 'resources';
    const WIDGET_ICON_SET = 'widgets';
    const DATA_ICON_SET = 'data';

    /**
     * @Gedmo\Slug(fields={"name"}, unique=true, updatable=false, separator="_")
     * @ORM\Column(unique=true)
     *
     * @var string
     *
     * @deprecated
     */
    private $cname;

    /**
     * @ORM\Column(name="is_default", type="boolean", options={"default"= 0})
     *
     * @var bool
     */
    private $default = false;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="IconItem", mappedBy="iconSet")
     *
     * @var ArrayCollection|IconItem[]
     */
    private $icons;

    public function __construct()
    {
        $this->refreshUuid();

        $this->icons = new ArrayCollection();
    }

    /**
     * @deprecated
     */
    public function getCname(): ?string
    {
        return $this->cname;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return IconItem[]
     */
    public function getIcons()
    {
        return $this->icons;
    }

    public function addIcon(IconItem $icon): void
    {
        if (!$this->icons->contains($icon)) {
            $this->icons->add($icon);
        }
    }

    public function removeIcon(IconItem $icon): void
    {
        if ($this->icons->contains($icon)) {
            $this->icons->removeElement($icon);
        }
    }
}
