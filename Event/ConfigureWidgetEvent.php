<?php

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

/**
 * Event dispatched when a widget is configured.
 */
class ConfigureWidgetEvent extends Event implements DataConveyorEventInterface
{
    private $isPopulated = false;
    private $instance;

    /**
     * Constructor.
     *
     * @param AbstractWorkspace $workspace
     */
    public function __construct(WidgetInstance $instance)
    {
        $this->instance = $instance;
    }

    public function setContent($content)
    {
        $this->isPopulated = true;
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
    
    public function getInstance()
    {
        return $this->instance;
    }
}
