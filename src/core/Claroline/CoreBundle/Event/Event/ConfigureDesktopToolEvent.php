<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Tool\Tool;

class ConfigureDesktopToolEvent extends Event implements DataConveyorEventInterface
{
    private $content;
    private $tool;
    private $isPopulated = false;

    /**
     * Constructor.
     */
    public function __construct(Tool $tool)
    {
        $this->tool = $tool;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function setContent($content)
    {
        $this->content = $content;
        $this->isPopulated = true;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
