<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Claroline\EvaluationBundle\Manager\WorkspaceRequirementsManager;
use Claroline\EvaluationBundle\Messenger\Message\AddWorkspaceRequirements;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class AddWorkspaceRequirementsHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var WorkspaceEvaluationManager */
    private $evaluationManager;
    /** @var WorkspaceRequirementsManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        WorkspaceEvaluationManager $evaluationManager,
        WorkspaceRequirementsManager $manager
    ) {
        $this->om = $om;
        $this->evaluationManager = $evaluationManager;
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
            $this->evaluationManager->getUserEvaluation($workspace, $user, true);
        }

        $this->om->endFlushSuite();

        // set required resources for the workspace
        $this->manager->manageRoleSubscription($role, $users, 'add');
    }
}
