<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class ForgotPasswordEvent extends Event
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
        return 'ForgotPasswordEvent';
    }

    public function getMessage()
    {
        return "L'utilisateur {$this->user->getUsername()} vient de faire une demande de mot de passe oublié.";
    }
}
