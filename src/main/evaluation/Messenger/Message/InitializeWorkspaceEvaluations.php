<?php

namespace Claroline\EvaluationBundle\Messenger\Message;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class InitializeWorkspaceEvaluations
{
    /** @var Workspace */
    private $workspace;
    /** @var User[] */
    private $users;

    public function __construct(Workspace $workspace, array $users)
    {
        $this->workspace = $workspace;
        $this->users = $users;
    }

    public function getWorkspace(): Workspace
    {
        return $this->workspace;
    }

    public function getUsers(): array
    {
        return $this->users;
    }
}
