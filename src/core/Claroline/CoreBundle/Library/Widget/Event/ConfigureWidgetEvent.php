<?php

namespace Claroline\CoreBundle\Library\Widget\Event;

use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * Event dispatched when a widget is configured.
 */
class ConfigureWidgetEvent extends Event
{
    private $widget;
    private $workspace;
    private $response;

    /**
     * Constructor.
     *
     * @param AbstractWorkspace $workspace
     * @param Widget $widget
     */
    public function __construct(AbstractWorkspace $workspace, Widget $widget)
    {
        $this->workspace = $workspace;
        $this->widget = $widget;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    public function getWidget()
    {
        return $this->widget;
    }

    public function setWidget($widget)
    {
        $this->widget = $widget;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}