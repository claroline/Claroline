<?php

namespace Icap\PortfolioBundle\Event;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Symfony\Component\EventDispatcher\Event;

class WidgetFindEvent extends Event
{
    /**
     * @var AbstractWidget
     */
    protected $widget;

    /**
     * @var int
     */
    protected $widgetId;

    /**
     * @var string
     */
    protected $widgetType;

    /**
     * @var User
     */
    protected $user;

    public function __construct($widgetId, $widgetType, User $user)
    {
        $this->widgetId = $widgetId;
        $this->widgetType = $widgetType;
        $this->user = $user;
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
     * @return WidgetFindEvent
     */
    public function setWidget(AbstractWidget $widget)
    {
        $this->widget = $widget;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidgetId()
    {
        return $this->widgetId;
    }

    /**
     * @return string
     */
    public function getWidgetType()
    {
        return $this->widgetType;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
