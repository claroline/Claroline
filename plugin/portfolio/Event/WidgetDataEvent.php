<?php

namespace Icap\PortfolioBundle\Event;

use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Symfony\Component\EventDispatcher\Event;

class WidgetDataEvent extends Event
{
    /**
     * @var string
     */
    protected $widgetType;

    /**
     * @var AbstractWidget
     */
    protected $widget;

    /**
     * @param string $widgetType
     */
    public function __construct($widgetType)
    {
        $this->widgetType = $widgetType;
    }

    /**
     * @return string
     */
    public function getWidgetType()
    {
        return $this->widgetType;
    }

    /**
     * @return AbstractWidget
     *
     * @throws \Exception
     */
    public function getWidget()
    {
        if (null === $this->widget) {
            throw new \Exception('Empty widget');
        }

        return $this->widget;
    }

    /**
     * @param AbstractWidget $widget
     *
     * @return WidgetDataEvent
     */
    public function setWidget(AbstractWidget $widget)
    {
        $this->widget = $widget;

        return $this;
    }
}
