<?php

namespace Claroline\CoreBundle\Event\Security;

use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserLogoutEvent extends Event
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
