<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Security;

use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class AuthenticationFailureEvent extends Event
{
    private $user;
    private $message;

    public function __construct($user, string $message)
    {
        $this->user = $user;
        $this->message = $message;
    }

    public function getUser(): ?User
    {
        if ($this->user instanceof User) {
            return $this->user;
        }

        return null;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
