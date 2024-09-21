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
use Claroline\CoreBundle\Entity\DataSource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * WidgetInstance entity.
 */
#[ORM\Table(name: 'claro_widget_instance')]
#[ORM\Entity]
class WidgetInstance
{
    use Id;
    use Uuid;

    /**
     * The widget which is rendered.
     *
     *
     * @var Widget
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Widget::class)]
    private $widget;

    /**
     * The parent container.
     *
     *
     * @var WidgetContainer
     */
    #[ORM\JoinColumn(name: 'container_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: WidgetContainer::class, inversedBy: 'instances', cascade: ['persist', 'remove', 'refresh'])]
    private $container;

    /**
     * @var WidgetInstanceConfig[]
     */
    #[ORM\OneToMany(targetEntity: WidgetInstanceConfig::class, mappedBy: 'widgetInstance', cascade: ['persist', 'remove'])]
    private $widgetInstanceConfigs;

    /**
     * The data source to fill the widget if any.
     *
     *
     * @var DataSource
     */
    #[ORM\JoinColumn(onDelete: 'CASCADE', nullable: true)]
    #[ORM\ManyToOne(targetEntity: DataSource::class)]
    private $dataSource = null;

    /**
     * WidgetContainer constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->widgetInstanceConfigs = new ArrayCollection();
    }

    /**
     * Get widget.
     *
     * @return Widget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * Set widget.
     */
    public function setWidget(Widget $widget)
    {
        $this->widget = $widget;
    }

    /**
     * Get widget container.
     *
     * @return WidgetContainer
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set widget container.
     *
     * @param WidgetContainer $container
     */
    public function setContainer(WidgetContainer $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get data source.
     *
     * @return DataSource
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Set data source.
     */
    public function setDataSource(DataSource $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    public function getWidgetInstanceConfigs()
    {
        return $this->widgetInstanceConfigs;
    }

    public function addWidgetInstanceConfig(WidgetInstanceConfig $config)
    {
        if (!$this->widgetInstanceConfigs->contains($config)) {
            $this->widgetInstanceConfigs->add($config);
        }
    }
}
