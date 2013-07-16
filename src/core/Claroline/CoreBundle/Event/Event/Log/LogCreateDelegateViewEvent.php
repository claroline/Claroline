<?php

namespace Claroline\CoreBundle\Event\Event\Log;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
/**
 *
 */
class LogCreateDelegateViewEvent extends Event implements DataConveyorEventInterface
{
    private $responseContent = '';
    private $log;
    private $isPopulated = false;

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
        $this->isPopulated = true;
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

    public function isPopulated()
    {
       return $this->isPopulated;
    }
}
