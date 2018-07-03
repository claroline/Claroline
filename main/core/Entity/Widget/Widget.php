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
use Claroline\CoreBundle\Entity\Plugin;
use Doctrine\ORM\Mapping as ORM;

/**
 * Widget entity.
 *
 * Describes a Widget provided by a plugin.
 *
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Widget\WidgetRepository")
 * @ORM\Table(name="claro_widget", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="widget_plugin_unique", columns={"name", "plugin_id"})
 * })
 */
class Widget
{
    use Id;
    use Uuid;
    use FromPlugin;

    const CONTEXT_DESKTOP = 'desktop';
    const CONTEXT_WORKSPACE = 'workspace';

    /**
     * The name of the widget.
     *
     * @ORM\Column()
     *
     * @var string
     */
    private $name;

    /**
     * The plugin that have introduced the widget.
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Plugin")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Plugin
     */
    private $plugin;

    /**
     * The class that holds the widget custom configuration if any.
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $class = null;

    /**
     * The list of DataSources accepted by the widget.
     *
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $sources = [];

    /**
     * The rendering context of the widget (workspace, desktop).
     *
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
    private $context = [
        self::CONTEXT_DESKTOP,
        self::CONTEXT_WORKSPACE,
    ];

    /**
     * @ORM\Column(name="is_exportable", type="boolean")
     *
     * @var bool
     */
    private $exportable;

    /**
     * A list of tags to group similar widgets.
     *
     * @ORM\Column(type="json_array")
     *
     * @var array
     */
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
     *
     * @param $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get plugin.
     *
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Set plugin.
     *
     * @param Plugin $plugin
     */
    public function setPlugin(Plugin $plugin)
    {
        $this->plugin = $plugin;
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

    /**
     * Set sources.
     *
     * @param array $sources
     */
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

    /**
     * Set context.
     *
     * @param array $context
     */
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
     *
     * @param $exportable
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

    /**
     * Set tags.
     *
     * @param array $tags
     */
    public function setTags(array $tags)
    {
        $this->tags = $tags;
    }
}
