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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Plugin;
use Doctrine\ORM\Mapping as ORM;

/**
 * Widget entity.
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
     * Abstract widgets require to be implemented by another widget
     * to be displayed.
     *
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    private $abstract = false;

    /**
     * The abstract widget which is implemented if any.
     *
     * @var Widget
     */
    private $parent = null;

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
     * Get widget class or the one from the parent if any.
     *
     * @return string
     */
    public function getClass()
    {
        if (!empty($this->class)) {
            return $this->class;
        }

        if (!empty($this->parent)) {
            return $this->parent->getClass();
        }

        return null;
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
     * Is it an abstract widget ?
     *
     * @return bool
     */
    public function isAbstract()
    {
        return $this->abstract;
    }

    /**
     * @param $abstract
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
    }

    /**
     * Get parent.
     *
     * @return Widget
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(self $widget = null)
    {
        $this->parent = $widget;
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
     * Get widget tags and the one from its parent if any.
     *
     * @return array
     */
    public function getTags()
    {
        if (!empty($this->parent)) {
            return array_merge($this->parent->getTags(), $this->tags);
        }

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
