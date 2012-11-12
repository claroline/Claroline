<?php

namespace Claroline\CoreBundle\Library\Widget\Event;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when a widget is configured.
 */
class ConfigureWidgetEvent extends Event
{
    private $workspace;
    private $content;
    private $isDefault;
    private $isDesktop;

    /**
     * Constructor.
     *
     * @param AbstractWorkspace $workspace
     */
    public function __construct($workspace, $isDefault = false, $isDesktop = false )
    {
        $this->workspace = $workspace;
        $this->isDefault = $isDefault;
        $this->isDesktop = $isDesktop;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace($workspace)
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

    public function isDefault()
    {
        return $this->isDefault;
    }

    public function isDesktop()
    {
        return $this->isDesktop;
    }

    public function setDefault($bool)
    {
        $this->isDefault = $bool;
    }

    public function setDesktop($bool)
    {
        $this->isDesktop = $bool;
    }
}