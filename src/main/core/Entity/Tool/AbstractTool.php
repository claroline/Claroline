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
 * @ORM\MappedSuperclass()
 */
abstract class AbstractTool
{
    use Id;
    use Uuid;
    use FromPlugin;

    /** @deprecated use Claroline\AppBundle\Component\Context\AdministrationContext::getName() */
    public const ADMINISTRATION = 'administration';
    /** @deprecated use Claroline\CoreBundle\Component\Context\WorkspaceContext::getName() */
    public const WORKSPACE = 'workspace';
    /** @deprecated use Claroline\AppBundle\Component\Context\DesktopContext::getName() */
    public const DESKTOP = 'desktop';

    /**
     * The name of the tool.
     *
     * @ORM\Column()
     */
    protected string $name;

    /**
     * The icon of the tool (For now, only the name of a FontAwesome icon is allowed).
     *
     * @ORM\Column()
     */
    protected ?string $class = null;

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

    /**
     * @deprecated use setIcon()
     */
    public function setClass(string $class = null): void
    {
        $this->setIcon($class);
    }

    public function getClass(): ?string
    {
        return $this->getIcon();
    }
}
