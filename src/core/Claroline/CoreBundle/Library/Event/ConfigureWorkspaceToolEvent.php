<?php

namespace Claroline\CoreBundle\Library\Event;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched by the resource controller when a resource copy is asked.
 */
class ConfigureWorkspaceToolEvent extends Event
{
    private $content;
    private $tool;
    private $workspace;

    /**
     * Constructor.
     *
     * @param AbstractResource $resource
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
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
}