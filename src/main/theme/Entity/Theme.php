<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ThemeBundle\Entity;

use Claroline\AppBundle\Entity\FromPlugin;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Doctrine\ORM\Mapping as ORM;

/**
 * Theme.
 *
 * @ORM\Entity(repositoryClass="Claroline\ThemeBundle\Repository\ThemeRepository")
 * @ORM\Table(name="claro_theme")
 */
class Theme
{
    use Id;
    use Uuid;
    use Name;
    use Description;
    use FromPlugin;

    use ThemeParameters;

    /**
     * Is it the default platform theme ?
     *
     * @ORM\Column(name="is_default", type="boolean")
     */
    private bool $default = false;

    /**
     * @ORM\Column(name="logo", nullable=true)
     */
    private ?string $logo = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Set default.
     */
    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

    /**
     * Is default ?
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * Returns a lowercase version of the theme name, with spaces replaced
     * by hyphens (used as an id or a file/directory name).
     */
    public function getNormalizedName(): string
    {
        return str_replace(' ', '-', strtolower($this->getName()));
    }
}
