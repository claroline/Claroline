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
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_icon_item')]
#[ORM\Entity]
class IconItem
{
    use Id;
    use Uuid;
    use Name;

    #[ORM\Column(name: 'mime_type', nullable: true)]
    private ?string $mimeType = null;

    #[ORM\Column(name: 'relative_url')]
    private ?string $relativeUrl = null;

    #[ORM\Column(type: 'boolean')]
    private bool $svg = false;

    #[ORM\JoinColumn(name: 'icon_set_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: \IconSet::class, inversedBy: 'icons', fetch: 'LAZY')]
    private ?IconSet $iconSet = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getIconSet(): ?IconSet
    {
        return $this->iconSet;
    }

    public function setIconSet(IconSet $iconSet): void
    {
        $this->iconSet = $iconSet;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType = null): void
    {
        $this->mimeType = $mimeType;
    }

    public function getRelativeUrl(): ?string
    {
        return $this->relativeUrl;
    }

    public function setRelativeUrl(string $relativeUrl): void
    {
        $this->relativeUrl = $relativeUrl;
    }

    public function isSvg(): bool
    {
        return $this->svg;
    }

    public function setSvg(bool $svg): void
    {
        $this->svg = $svg;
    }
}
