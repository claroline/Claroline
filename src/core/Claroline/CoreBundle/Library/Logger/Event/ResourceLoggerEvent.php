<?php

namespace Claroline\CoreBundle\Library\Logger\Event;

use Symfony\Component\EventDispatcher\Event;

class ResourceLoggerEvent extends Event
{
    private $instance;
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
     *
     * @param integer $resourceId
     */
    public function __construct($instance, $action, $logDescr = null, $url = null)
    {
        $this->instance = $instance;
        $this->action = $action;
        $this->logDescr = $logDescr;
        $this->url = $url;
    }

    /**
     * Returns the id of the resource on which the action is to be taken.
     *
     * @return integer
     */
    public function getInstance()
    {
        return $this->instance;
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