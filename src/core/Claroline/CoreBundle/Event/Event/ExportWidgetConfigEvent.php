<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class ExportWidgetConfigEvent extends Event implements DataConveyorEventInterface
{
    private $config;
    private $workspace;
    private $widget;
    private $isPopulated = false;

    public function __construct(Widget $widget, AbstractWorkspace $workspace)
    {
        $this->widget = $widget;
        $this->workspace = $workspace;
    }

    public function setConfig(array $config)
    {
        $this->isPopulated = true;
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

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
