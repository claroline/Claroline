<?php

namespace Icap\PortfolioBundle\Event;

use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Symfony\Component\EventDispatcher\Event;

class WidgetViewEvent extends Event
{
    /**
     * @var string
     */
    protected $view;

    /**
     * @var string
     */
    protected $widgetType;

    /**
     * @var AbstractWidget
     */
    protected $widget;

    /**
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param string $view
     *
     * @return WidgetViewEvent
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return string
     */
    public function getWidgetType()
    {
        return $this->widgetType;
    }

    /**
     * @param string $widgetType
     *
     * @return WidgetViewEvent
     */
    public function setWidgetType($widgetType)
    {
        $this->widgetType = $widgetType;

        return $this;
    }

    /**
     * @return AbstractWidget
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param AbstractWidget $widget
     *
     * @return WidgetViewEvent
     */
    public function setWidget(AbstractWidget $widget)
    {
        $this->widget = $widget;

        return $this;
    }
}
