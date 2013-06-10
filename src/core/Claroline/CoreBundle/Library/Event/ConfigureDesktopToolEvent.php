<?php

namespace Claroline\CoreBundle\Library\Event;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Symfony\Component\EventDispatcher\Event;

class ConfigureDesktopToolEvent extends Event
{
    private $content;
    private $tool;

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
    }

    public function getContent()
    {
        return $this->content;
    }
}