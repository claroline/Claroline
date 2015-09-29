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
     * @param string $widgetType
     */
    public function __construct($widgetType)
    {
        $this->widgetType = $widgetType;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getFormView()
    {
        if (null === $this->formView) {
            throw new \Exception("Empty form view");
        }
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
}
