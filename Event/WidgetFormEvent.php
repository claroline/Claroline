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

    /**
     * @return AbstractWidget
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param AbstractWidget $data
     *
     * @return WidgetFormEvent
     */
    public function setData(AbstractWidget $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
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
