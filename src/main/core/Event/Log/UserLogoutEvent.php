<?php

namespace Claroline\CoreBundle\Event\Log;

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

    public function getEvent()
    {
        return 'UserLogoutEvent';
    }

    public function getMessage()
    {
        return "L'utilisateur {$this->user->getUsername()} vient de se déconnecter.";
    }
}
