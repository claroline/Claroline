<?php

namespace Claroline\CoreBundle\Library\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched by the resource controller when a resource creation form is asked.
 */
class CreateFormResourceEvent extends Event
{
    private $responseContent = '';

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
}