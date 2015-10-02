<?php

namespace Icap\PortfolioBundle\Event;

use Icap\PortfolioBundle\Entity\Widget\WidgetType;
use Symfony\Component\EventDispatcher\Event;

class WidgetTypeCreateEvent extends Event
{
    protected $widgetType;

    /**
     * @return WidgetType
     * @throws \Exception
     */
    public function getWidgetType()
    {
        if (null === $this->widgetType) {
            throw new \Exception("Empty vidget type");
        }
        return $this->widgetType;
    }

    /**
     * @param WidgetType $widgetType
     *
     * @return WidgetTypeCreateEvent
     */
    public function setWidgetType(WidgetType $widgetType)
    {
        $this->widgetType = $widgetType;

        return $this;
    }
}
