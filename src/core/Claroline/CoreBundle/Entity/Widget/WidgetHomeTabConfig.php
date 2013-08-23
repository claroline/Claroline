<?php

namespace Claroline\CoreBundle\Entity\Widget;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_widget_home_tab_config",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="widget_home_tab_unique_order", columns={"widget_id", "home_tab_id", "widget_order"})
 *     })
 */
class WidgetHomeTabConfig
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Widget\Widget"
     * )
     * @ORM\JoinColumn(name="widget_id", onDelete="CASCADE", nullable=false)
     */
    protected $widget;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Home\HomeTab"
     * )
     * @ORM\JoinColumn(name="home_tab_id", onDelete="CASCADE", nullable=false)
     */
    protected $homeTab;

    /**
     * @ORM\Column(name="widget_order", nullable=false)
     */
    protected $widgetOrder;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getWidget()
    {
        return $this->widget;
    }

    public function setWidget(Widget $widget)
    {
        $this->widget = $widget;
    }

    public function getHomeTab()
    {
        return $this->homeTab;
    }

    public function setHomeTab(HomeTab $homeTab)
    {
        $this->homeTab = $homeTab;
    }

    public function getWidgetOrder()
    {
        return $this->widgetOrder;
    }

    public function setWidgetOrder($widgetOrder)
    {
        $this->widgetOrder = $widgetOrder;
    }
}
