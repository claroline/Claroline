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
use Doctrine\Common\Collections\Collection;
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
     */
    #[ORM\ManyToOne(targetEntity: Widget::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Widget $widget = null;

    /**
     * The parent container.
     */
    #[ORM\ManyToOne(targetEntity: WidgetContainer::class, inversedBy: 'instances')]
    #[ORM\JoinColumn(name: 'container_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?WidgetContainer $container = null;

    /**
     * @var Collection<int, WidgetInstanceConfig>
     */
    #[ORM\OneToMany(targetEntity: WidgetInstanceConfig::class, mappedBy: 'widgetInstance', cascade: ['persist', 'remove'])]
    private Collection $widgetInstanceConfigs;

    /**
     * The data source to fill the widget if any.
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: DataSource::class)]
    private ?DataSource $dataSource = null;

    public function __construct()
    {
        $this->refreshUuid();

        $this->widgetInstanceConfigs = new ArrayCollection();
    }

    public function getWidget(): ?Widget
    {
        return $this->widget;
    }

    public function setWidget(Widget $widget): void
    {
        $this->widget = $widget;
    }

    public function getContainer(): ?WidgetContainer
    {
        return $this->container;
    }

    public function setContainer(?WidgetContainer $container = null): void
    {
        $this->container = $container;
    }

    public function getDataSource(): ?DataSource
    {
        return $this->dataSource;
    }

    public function setDataSource(?DataSource $dataSource): void
    {
        $this->dataSource = $dataSource;
    }

    public function getWidgetInstanceConfigs(): Collection
    {
        return $this->widgetInstanceConfigs;
    }

    public function addWidgetInstanceConfig(WidgetInstanceConfig $config): void
    {
        if (!$this->widgetInstanceConfigs->contains($config)) {
            $this->widgetInstanceConfigs->add($config);
        }
    }
}
