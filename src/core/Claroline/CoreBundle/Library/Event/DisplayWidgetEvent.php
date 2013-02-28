<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class DisplayWidgetEvent extends Event
{
    protected $content;
    protected $workspace;

    public function __construct(AbstractWorkspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }
}
