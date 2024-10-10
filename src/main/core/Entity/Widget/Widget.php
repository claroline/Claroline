<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Widget;

use Claroline\AppBundle\Entity\FromPlugin;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Repository\Widget\WidgetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Widget entity.
 *
 * Describes a Widget provided by a plugin.
 */
#[ORM\Table(name: 'claro_widget')]
#[ORM\UniqueConstraint(name: 'widget_plugin_unique', columns: ['name', 'plugin_id'])]
#[ORM\Entity(repositoryClass: WidgetRepository::class)]
class Widget
{
    use Id;
    use Uuid;
    use FromPlugin;

    /** @deprecated use Claroline\CoreBundle\Component\Context\DesktopContext::getName() */
    public const CONTEXT_DESKTOP = 'desktop';
    /** @deprecated use Claroline\CoreBundle\Component\Context\WorkspaceContext::getName() */
    public const CONTEXT_WORKSPACE = 'workspace';
    /** @deprecated use Claroline\CoreBundle\Component\Context\AdministrationContext::getName() */
    public const CONTEXT_ADMINISTRATION = 'administration';
    /** @deprecated use Claroline\CoreBundle\Component\Context\PublicContext::getName() */
    public const CONTEXT_HOME = 'public';

    #[ORM\Column]
    private ?string $name = null;

    /**
     * The class that holds the widget custom configuration if any.
     */
    #[ORM\Column(nullable: true)]
    private ?string $class = null;

    /**
     * The list of DataSources accepted by the widget.
     */
    #[ORM\Column(type: Types::JSON)]
    private ?array $sources = [];

    /**
     * The rendering context of the widget (workspace, desktop).
     */
    #[ORM\Column(type: Types::JSON)]
    private ?array $context = [
        self::CONTEXT_DESKTOP,
        self::CONTEXT_WORKSPACE,
        self::CONTEXT_ADMINISTRATION,
        self::CONTEXT_HOME,
    ];

    #[ORM\Column(name: 'is_exportable', type: Types::BOOLEAN)]
    private bool $exportable = false;

    /**
     * A list of tags to group similar widgets.
     */
    #[ORM\Column(type: Types::JSON)]
    private ?array $tags = [];

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): void
    {
        $this->class = $class;
    }

    public function getSources(): ?array
    {
        return $this->sources;
    }

    public function setSources(array $sources): void
    {
        $this->sources = $sources;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    public function isExportable(): bool
    {
        return $this->exportable;
    }

    public function setExportable(bool $exportable): void
    {
        $this->exportable = $exportable;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }
}
