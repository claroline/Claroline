<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class DisplayToolEvent extends Event
{
    protected $response;
    protected $content;

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
