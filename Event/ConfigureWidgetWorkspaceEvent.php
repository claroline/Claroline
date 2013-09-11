<?php

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * Event dispatched when a widget is configured.
 */
class ConfigureWidgetWorkspaceEvent extends Event implements DataConveyorEventInterface
{
    private $workspace;
    private $content;
    private $isDefault;
    private $isPopulated = false;

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
        $this->isPopulated = true;
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

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
