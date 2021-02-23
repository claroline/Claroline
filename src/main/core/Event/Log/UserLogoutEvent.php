<?php

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    public function getMessage(TranslatorInterface $translator)
    {
        return sprintf($translator->trans('userLogout', [], 'security'), $this->user->getUsername());
    }
}
