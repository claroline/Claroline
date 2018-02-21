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

use Claroline\AppBundle\Event\DataConveyorEventInterface;
use Claroline\AppBundle\Event\MandatoryEventInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class OpenAdministrationToolEvent extends Event implements DataConveyorEventInterface, MandatoryEventInterface
{
    private $response;
    protected $isPopulated = false;
    private $toolName;

    public function __construct($toolName)
    {
        $this->toolName = $toolName;
    }

    public function setResponse(Response $response)
    {
        $this->isPopulated = true;
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }

    public function getToolName()
    {
        return $this->toolName;
    }
}
