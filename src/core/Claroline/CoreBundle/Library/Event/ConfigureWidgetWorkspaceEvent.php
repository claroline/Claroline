<?php

namespace Claroline\CoreBundle\Library\Event;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when a widget is configured.
 */
class ConfigureWidgetWorkspaceEvent extends Event
{
    private $workspace;
    private $content;
    private $isDefault;

    /**
     * Constructor.
     *
     * @param AbstractWorkspace $workspace
     */
    public function __construct($workspace, $isDefault = false)
    {
        $this->workspace = $workspace;
        $this->isDefault = $isDefault;
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

    public function setDefault($bool)
    {
        $this->isDefault = $bool;
    }
}