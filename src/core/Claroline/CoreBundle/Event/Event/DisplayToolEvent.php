<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class DisplayToolEvent extends Event implements DataConveyorEventInterface
{
    protected $response;
    protected $content;
    protected $isPopulated = false;

    public function __construct(AbstractWorkspace $workspace = null)
    {
        $this->workspace = $workspace;
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

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
