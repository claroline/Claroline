<?php

namespace Icap\PortfolioBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class WidgetFormViewEvent extends Event
{
    /**
     * @var string
     */
    protected $formView;

    /**
     * @var string
     */
    protected $widgetType;

    /**
     * @return string
     */
    public function getFormView()
    {
        return $this->formView;
    }

    /**
     * @param string $formView
     *
     * @return WidgetFormViewEvent
     */
    public function setFormView($formView)
    {
        $this->formView = $formView;

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
     * @return WidgetFormViewEvent
     */
    public function setWidgetType($widgetType)
    {
        $this->widgetType = $widgetType;

        return $this;
    }
}
