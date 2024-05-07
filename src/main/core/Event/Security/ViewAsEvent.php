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

class ViewAsEvent extends Event
{
    public function __construct(
        private readonly User $user,
        private readonly string $role
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRole(): string
    {
        return $this->role;
    }
}
