<?php

namespace Claroline\EvaluationBundle\Messenger\Message;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class AddWorkspaceRequirements
{
    /** @var Workspace */
    private $workspace;
    /** @var Role */
    private $role;
    /** @var User[] */
    private $users;

    public function __construct(Workspace $workspace, Role $role, array $users)
    {
        $this->workspace = $workspace;
        $this->role = $role;
        $this->users = $users;
    }

    public function getWorkspace(): Workspace
    {
        return $this->workspace;
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
