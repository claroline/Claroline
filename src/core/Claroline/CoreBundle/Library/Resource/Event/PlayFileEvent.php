<?php

namespace Claroline\CoreBundle\Library\Resource\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class PlayFileEvent extends Event
{
    private $file;
    private $response;

    /**
     * Constructor.
     *
     * @param integer $resourceId
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Returns the id of the resource on which the action is to be taken.
     *
     * @return integer
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets the response for the action.
     *
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Returns the response for the action.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}