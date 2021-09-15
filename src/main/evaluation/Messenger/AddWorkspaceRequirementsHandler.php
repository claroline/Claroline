<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\Workspace\EvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\AddWorkspaceRequirements;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddWorkspaceRequirementsHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var EvaluationManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        EvaluationManager $manager
    ) {
        $this->om = $om;
        $this->manager = $manager;
    }

    public function __invoke(AddWorkspaceRequirements $requirements)
    {
        $workspace = $requirements->getWorkspace();
        $users = $requirements->getUsers();
        $role = $requirements->getRole();

        $this->om->startFlushSuite();

        // initialize workspace evaluations (maybe move it elsewhere for separation of concern)
        foreach ($users as $user) {
            $this->manager->getEvaluation($workspace, $user, true);
        }

        // set required resources for the workspace
        $this->manager->manageRoleSubscription($role, $users, 'add');

        $this->om->endFlushSuite();
    }
}
