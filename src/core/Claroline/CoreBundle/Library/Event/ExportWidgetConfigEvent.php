<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class ExportWidgetConfigEvent extends Event
{
    private $config;
    private $workspace;
    private $widget;

    public function __construct(Widget $widget, AbstractWorkspace $workspace)
    {
        $this->widget = $widget;
        $this->workspace = $workspace;
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getWidget()
    {
        return $this->widget;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }
}

