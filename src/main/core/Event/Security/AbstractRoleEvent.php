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

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractRoleEvent extends Event
{
    /** @var User[] */
    private $users;
    /** @var Role */
    private $role;

    public function __construct(array $users, Role $role)
    {
        $this->users = $users;
        $this->role = $role;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function getRole()
    {
        return $this->role;
    }
}
