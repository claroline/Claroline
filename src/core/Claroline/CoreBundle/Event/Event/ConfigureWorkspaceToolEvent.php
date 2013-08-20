<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class ConfigureWorkspaceToolEvent extends Event implements DataConveyorEventInterface
{
    private $content;
    private $tool;
    private $workspace;
    private $isPopulated = false;

    /**
     * Constructor.
     */
    public function __construct(Tool $tool, AbstractWorkspace $workspace)
    {
        $this->tool = $tool;
        $this->workspace = $workspace;
    }

    public function getTool()
    {
        return $this->tool;
    }

    public function getWorkspace()
    {
        return $this->workspace;
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
}
