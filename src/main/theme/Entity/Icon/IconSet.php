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

use Doctrine\DBAL\Types\Types;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Name;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'claro_icon_set')]
#[ORM\Entity]
class IconSet
{
    use Id;
    use Uuid;
    use Name;

    public const RESOURCE_ICON_SET = 'resources';
    public const WIDGET_ICON_SET = 'widgets';
    public const DATA_ICON_SET = 'data';

    /**
     * @deprecated
     */
    #[ORM\Column(unique: true)]
    #[Gedmo\Slug(fields: ['name'], updatable: false, unique: true, separator: '_')]
    private ?string $cname = null;

    #[ORM\Column(name: 'is_default', type: Types::BOOLEAN, options: ['default' => 0])]
    private bool $default = false;

    #[ORM\Column(nullable: true)]
    private ?string $type = null;

    #[ORM\OneToMany(mappedBy: 'iconSet', targetEntity: IconItem::class)]
    private Collection $icons;

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
     * @return Collection|IconItem[]
     */
    public function getIcons(): Collection
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
