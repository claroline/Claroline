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
use Doctrine\ORM\Mapping as ORM;

/**
 * Widget entity.
 *
 * Describes a Widget provided by a plugin.
 */
#[ORM\Table(name: 'claro_widget')]
#[ORM\UniqueConstraint(name: 'widget_plugin_unique', columns: ['name', 'plugin_id'])]
#[ORM\Entity(repositoryClass: \Claroline\CoreBundle\Repository\Widget\WidgetRepository::class)]
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

    /**
     * The name of the widget.
     *
     *
     * @var string
     */
    #[ORM\Column]
    private $name;

    /**
     * The class that holds the widget custom configuration if any.
     *
     *
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $class;

    /**
     * The list of DataSources accepted by the widget.
     *
     *
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private $sources = [];

    /**
     * The rendering context of the widget (workspace, desktop).
     *
     *
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private $context = [
        self::CONTEXT_DESKTOP,
        self::CONTEXT_WORKSPACE,
        self::CONTEXT_ADMINISTRATION,
        self::CONTEXT_HOME,
    ];

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_exportable', type: 'boolean')]
    private $exportable;

    /**
     * A list of tags to group similar widgets.
     *
     *
     * @var array
     */
    #[ORM\Column(type: 'json')]
    private $tags = [];

    /**
     * Widget constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get widget class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set class.
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Get sources.
     *
     * @return array
     */
    public function getSources()
    {
        return $this->sources;
    }

    public function setSources(array $sources)
    {
        $this->sources = $sources;
    }

    /**
     * Get the rendering context of the widget (workspace, desktop).
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    public function setContext(array $context)
    {
        $this->context = $context;
    }

    /**
     * Is widget exportable ?
     *
     * @return bool
     */
    public function isExportable()
    {
        return $this->exportable;
    }

    /**
     * Set exportable.
     */
    public function setExportable($exportable)
    {
        $this->exportable = $exportable;
    }

    /**
     * Get tags.
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }
}
