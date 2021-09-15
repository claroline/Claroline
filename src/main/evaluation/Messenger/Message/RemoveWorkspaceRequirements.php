<?php

namespace Claroline\EvaluationBundle\Messenger\Message;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;

class RemoveWorkspaceRequirements
{
    /** @var Role */
    private $role;
    /** @var User[] */
    private $users;

    public function __construct(Role $role, array $users)
    {
        $this->role = $role;
        $this->users = $users;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function getUsers(): array
    {
        return $this->users;
    }
}
