<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Tool;

use Claroline\AppBundle\Entity\FromPlugin;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(
 *      name="claro_tools",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="tool_plugin_unique",columns={"name", "plugin_id"})}
 * )
 */
class Tool
{
    use Id;
    use Uuid;
    use FromPlugin;

    /**
     * The name of the tool.
     *
     * @ORM\Column()
     */
    private string $name;

    /**
     * The icon of the tool (For now, only the name of a FontAwesome icon is allowed).
     *
     * @ORM\Column()
     */
    private ?string $class = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIcon(): ?string
    {
        return $this->class;
    }

    public function setIcon(string $icon = null): void
    {
        $this->class = $icon;
    }
}
