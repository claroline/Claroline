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
use Symfony\Component\HttpFoundation\Response;

/**
 * Event dispatched by the administration page when the administration page of a plugin is asked.
 */
class PluginOptionsEvent extends Event implements DataConveyorEventInterface, MandatoryEventInterface
{
    protected $response;
    private $isPopulated = false;

    public function setResponse(Response $response)
    {
        $this->isPopulated = true;
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Checks if the event has been populated.
     *
     * @return bool
     */
    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
