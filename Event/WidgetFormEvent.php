<?php

namespace Icap\PortfolioBundle\Event;

use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;

class WidgetFormEvent extends Event
{
    /**
     * @var string
     */
    protected $widgetType;

    /**
     * @var AbstractWidget
     */
    protected $data;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @param string         $widgetType
     * @param AbstractWidget $widget
     */
    public function __construct($widgetType, AbstractWidget $widget)
    {
        $this->widgetType = $widgetType;
        $this->data = $widget;
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
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return Form
     * @throws \Exception
     */
    public function getForm()
    {
        if (null === $this->form) {
            throw new \Exception("Empty form");
        }
        return $this->form;
    }

    /**
     * @param Form $form
     *
     * @return WidgetFormEvent
     */
    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }
}
