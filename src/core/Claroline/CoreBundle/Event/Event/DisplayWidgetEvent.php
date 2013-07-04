<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class DisplayWidgetEvent extends Event
{
    protected $content;
    protected $title;
    protected $workspace;

    public function __construct(AbstractWorkspace $workspace = null)
    {
        $this->workspace = $workspace;
        $this->content = null;
        $this->title = null;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function hasContent()
    {
        return $this->content !== null;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function hasTitle()
    {
        return $this->title !== null;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }
}
