<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;

class ResourceLogEvent extends Event
{
    private $resource;
    private $action;
    private $logDescr;
    private $url;

    const DELETE_ACTION = 'delete';
    const CREATE_ACTION = 'create';
    const CUSTOM_ACTION = 'custom';
    const EXPORT_ACTION = 'export';
    const MOVE_ACTION = 'move';
    const COPY_ACTION = 'copy';

    /**
     * Constructor.
     */
    public function __construct($resource, $action, $logDescr = null, $url = null)
    {
        $this->resource = $resource;
        $this->action = $action;
        $this->logDescr = $logDescr;
        $this->url = $url;
    }

    /**
     * Returns the resource on which the action is to be taken.
     */
    public function getResource()
    {
        return $this->resource;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getLogDescription()
    {
        return $this->logDescr;
    }

    public function getUrl()
    {
        return $this->url;
    }
}