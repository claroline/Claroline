<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;

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
     * Returns the response content (creation form as string).
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
