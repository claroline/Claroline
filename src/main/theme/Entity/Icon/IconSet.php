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
use Claroline\AppBundle\Entity\Restriction\Locked;
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
    use Locked;

    const RESOURCE_ICON_SET = 'resource_icon_set';
    const WIDGET_ICON_SET = 'widget_icon_set';
    const DATA_ICON_SET = 'data_icon_set';

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

    public function __construct()
    {
        $this->refreshUuid();

        $this->icons = new ArrayCollection();
    }

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

    public function getIcons()
    {
        return $this->icons;
    }

    /**
     * @param array $icons
     *
     * @deprecated
     */
    public function setIcons($icons): void
    {
        $this->icons = $icons;
    }

    public function addIcon(IconItem $icon)
    {
        $this->icons->add($icon);
    }
}
