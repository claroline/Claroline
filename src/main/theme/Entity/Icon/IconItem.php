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

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_icon_item")
 */
class IconItem
{
    use Id;
    use Uuid;
    use Name;

    /**
     * @ORM\Column(name="mime_type", nullable=true)
     *
     * @var string
     */
    private $mimeType;

    /**
     * @ORM\Column(name="relative_url")
     *
     * @var string
     */
    private $relativeUrl;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $svg = false;

    /**
     * @ORM\ManyToOne(targetEntity="IconSet", inversedBy="icons", fetch="LAZY")
     * @ORM\JoinColumn(name="icon_set_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var IconSet
     */
    private $iconSet;

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

    public function setMimeType(?string $mimeType = null)
    {
        $this->mimeType = $mimeType;

        return $this;
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
