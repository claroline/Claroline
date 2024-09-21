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

use Doctrine\DBAL\Types\Types;
use Claroline\ThemeBundle\Repository\ThemeRepository;
use Claroline\AppBundle\Entity\FromPlugin;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Disabled;
use Claroline\AppBundle\Entity\Meta\Name;
use Doctrine\ORM\Mapping as ORM;

/**
 * Theme.
 */
#[ORM\Table(name: 'claro_theme')]
#[ORM\Entity(repositoryClass: ThemeRepository::class)]
class Theme
{
    use Id;
    use Uuid;
    use Name;
    use Description;
    use Disabled;
    use FromPlugin;

    use ThemeParameters;

    /**
     * Is it the default platform theme ?
     */
    #[ORM\Column(name: 'is_default', type: Types::BOOLEAN)]
    private bool $default = false;

    #[ORM\Column(nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(nullable: true)]
    private ?string $primaryColor = null;

    #[ORM\Column(nullable: true)]
    private ?string $secondaryColor = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

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

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(?string $primaryColor): void
    {
        $this->primaryColor = $primaryColor;
    }

    public function getSecondaryColor(): ?string
    {
        return $this->secondaryColor;
    }

    public function setSecondaryColor(?string $secondaryColor): void
    {
        $this->secondaryColor = $secondaryColor;
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
