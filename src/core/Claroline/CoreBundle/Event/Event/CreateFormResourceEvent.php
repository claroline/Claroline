<?php

namespace Claroline\CoreBundle\Event\Event;

use Symfony\Component\EventDispatcher\Event;
use Claroline\CoreBundle\Event\DataConveyorEventInterface;
use Claroline\CoreBundle\Event\MandatoryEventInterface;

/**
 * Event dispatched by the resource controller when a resource creation form is asked.
 */
class CreateFormResourceEvent extends Event implements DataConveyorEventInterface, MandatoryEventInterface
{
    private $responseContent = '';
    private $isPopulated = false;

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

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
