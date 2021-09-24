<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\CoreBundle\Manager\Workspace\EvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\RemoveWorkspaceRequirements;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RemoveWorkspaceRequirementsHandler implements MessageHandlerInterface
{
    /** @var EvaluationManager */
    private $manager;

    public function __construct(
        EvaluationManager $manager
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
