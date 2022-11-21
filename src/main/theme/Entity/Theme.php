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

    /**
     * Is it the default platform theme ?
     *
     * @ORM\Column(name="is_default", type="boolean")
     *
     * @var bool
     */
    private $default = false;

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

    public function isCustom(): bool
    {
        return empty($this->plugin);
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
