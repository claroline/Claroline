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
     * @param string         $widgetType
     * @param AbstractWidget $widget
     */
    public function __construct($widgetType, AbstractWidget $widget)
    {
        $this->widgetType = $widgetType;
        $this->widget = $widget;
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getView()
    {
        if (null === $this->view) {
            throw new \Exception('Empty view');
        }

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
     * @return AbstractWidget
     */
    public function getWidget()
    {
        return $this->widget;
    }
}
