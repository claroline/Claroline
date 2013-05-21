<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * 
 */
class LogCreateDelegateViewEvent extends Event
{
    private $responseContent = '';
    private $log;

    public function __construct($log)
    {
        $this->log = $log;
    }

    /**
     * Sets the response content (creation form as string).
     *
     * @param string $responseContent
     */
    public function setResponseContent($responseContent)
    {
        $this->responseContent = $responseContent;
    }

    /**
     * Returns the response content (creation form as string)
     *
     * @return string
     */
    public function getResponseContent()
    {
        return $this->responseContent;
    }

    public function getLog()
    {
        return $this->log;
    }
}