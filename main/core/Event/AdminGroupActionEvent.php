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
use Claroline\CoreBundle\Entity\Group;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class AdminGroupActionEvent extends Event implements MandatoryEventInterface, DataConveyorEventInterface
{
    private $group;
    private $response;
    private $isPopulated;

    public function __construct(Group $group)
    {
        $this->group = $group;
        $this->isPopulated = false;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setResponse(Response $response)
    {
        $this->response = $response;
        $this->isPopulated = true;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function isPopulated()
    {
        return $this->isPopulated;
    }
}
