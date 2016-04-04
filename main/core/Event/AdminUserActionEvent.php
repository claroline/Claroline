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

use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class AdminUserActionEvent extends Event implements MandatoryEventInterface, DataConveyorEventInterface
{
    private $user;
    private $response;
    private $isPopulated;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->isPopulated = false;
    }

    public function getUser()
    {
        return $this->user;
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
