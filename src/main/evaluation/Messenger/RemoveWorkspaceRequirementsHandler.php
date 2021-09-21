<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\EvaluationBundle\Manager\WorkspaceRequirementsManager;
use Claroline\EvaluationBundle\Messenger\Message\RemoveWorkspaceRequirements;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RemoveWorkspaceRequirementsHandler implements MessageHandlerInterface
{
    /** @var WorkspaceRequirementsManager */
    private $manager;

    public function __construct(
        WorkspaceRequirementsManager $manager
    ) {
        $this->manager = $manager;
    }

    public function __invoke(RemoveWorkspaceRequirements $requirements)
    {
        $users = $requirements->getUsers();
        $role = $requirements->getRole();

        $this->manager->manageRoleSubscription($role, $users, 'remove');
    }
}
