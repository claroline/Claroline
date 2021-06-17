<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Requirements;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\UserEvaluationEvent;
use Claroline\EvaluationBundle\Entity\Evaluation\AbstractEvaluation;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EvaluationManager
{
    /** @var ObjectManager */
    private $om;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    private $logConnectRepo;
    private $evaluationRepo;
    private $requirementsRepo;
    private $resourceUserEvalRepo;

    /**
     * EvaluationManager constructor.
     */
    public function __construct(
        ObjectManager $om,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;

        $this->logConnectRepo = $this->om->getRepository(LogConnectWorkspace::class);
        $this->evaluationRepo = $om->getRepository(Evaluation::class);
        $this->requirementsRepo = $om->getRepository(Requirements::class);
        $this->resourceUserEvalRepo = $om->getRepository(ResourceUserEvaluation::class);
    }

    /**
     * Retrieve or create evaluation for a workspace and an user.
     *
     * @param bool $withCreation
     *
     * @return Evaluation|null
     *
     * @throws \Exception
     */
    public function getEvaluation(Workspace $workspace, User $user, $withCreation = true)
    {
        $evaluation = $this->evaluationRepo->findOneBy(['workspace' => $workspace, 'user' => $user]);

        if ($withCreation && empty($evaluation)) {
            $evaluation = new Evaluation();
            $evaluation->setWorkspace($workspace);
            $evaluation->setWorkspaceCode($workspace->getCode());
            $evaluation->setUser($user);
            $evaluation->setUserName($user->getLastName().' '.$user->getFirstName());
            $evaluation->setDate(new \DateTime());
            $evaluation->setStatus(AbstractEvaluation::STATUS_NOT_ATTEMPTED);
            $this->om->persist($evaluation);
            $this->om->flush();
        }

        return $evaluation;
    }

    /**
     * Retrieve the list of resources an user has to do in the workspace.
     *
     * @return array
     */
    public function computeResourcesToDo(Workspace $workspace, User $user)
    {
        $resources = [];

        $userRoles = $user->getEntityRoles();
        $roles = array_filter($userRoles, function (Role $role) use ($workspace) {
            return $role->getWorkspace() && $role->getWorkspace()->getUuid() === $workspace->getUuid();
        });

        // Retrieves resources that have to be done by the user in the workspace
        /** @var Requirements $userRequirements */
        $userRequirements = $this->requirementsRepo->findOneBy(['workspace' => $workspace, 'user' => $user]);

        if ($userRequirements) {
            foreach ($userRequirements->getResources() as $node) {
                $resources[$node->getUuid()] = $node;
            }
        }

        // Retrieves resources that have to be done by the roles that the user has in the workspace
        foreach ($roles as $role) {
            /** @var Requirements $roleRequirements */
            $roleRequirements = $this->requirementsRepo->findOneBy(['workspace' => $workspace, 'role' => $role]);

            if ($roleRequirements) {
                foreach ($roleRequirements->getResources() as $node) {
                    $resources[$node->getUuid()] = $node;
                }
            }
        }

        return $resources;
    }

    /**
     * Compute evaluation status and progression of an user in a workspace.
     *
     * @param \DateTime $date
     *
     * @return Evaluation|null
     *
     * @throws \Exception
     */
    public function computeEvaluation(Workspace $workspace, User $user, ResourceUserEvaluation $currentRue = null, $date = null)
    {
        $evaluation = $this->getEvaluation($workspace, $user);

        $statusCount = [
            AbstractEvaluation::STATUS_PASSED => 0,
            AbstractEvaluation::STATUS_FAILED => 0,
            AbstractEvaluation::STATUS_COMPLETED => 0,
            AbstractEvaluation::STATUS_INCOMPLETE => 0,
            AbstractEvaluation::STATUS_NOT_ATTEMPTED => 0,
            AbstractEvaluation::STATUS_UNKNOWN => 0,
            AbstractEvaluation::STATUS_OPENED => 0,
            AbstractEvaluation::STATUS_PARTICIPATED => 0,
            AbstractEvaluation::STATUS_TODO => 0,
        ];
        $resources = $this->computeResourcesToDo($workspace, $user);

        $score = 0;
        $scoreMax = 0;
        $progressionMax = count($resources);

        // if there is a triggering resource evaluation checks if is part of the workspace requirements
        // if not, no evaluation is computed
        if ($currentRue) {
            $currentResourceId = $currentRue->getResourceNode()->getUuid();

            if (isset($resources[$currentResourceId])) {
                if ($currentRue->getStatus()) {
                    ++$statusCount[$currentRue->getStatus()];
                    $score += $currentRue->getScore() ?? 0;
                    $scoreMax += $currentRue->getScoreMax() ?? 0;
                }
                unset($resources[$currentResourceId]);
            }
        }

        foreach ($resources as $resource) {
            $resourceEval = $this->resourceUserEvalRepo->findOneBy(['resourceNode' => $resource, 'user' => $user]);

            if ($resourceEval && $resourceEval->getStatus()) {
                ++$statusCount[$resourceEval->getStatus()];
                $score += $resourceEval->getScore() ?? 0;
                $scoreMax += $resourceEval->getScoreMax() ?? 0;
            }
        }

        $progression = $statusCount[AbstractEvaluation::STATUS_PASSED] +
            $statusCount[AbstractEvaluation::STATUS_FAILED] +
            $statusCount[AbstractEvaluation::STATUS_COMPLETED] +
            $statusCount[AbstractEvaluation::STATUS_PARTICIPATED];

        $status = AbstractEvaluation::STATUS_INCOMPLETE;
        if (0 !== $statusCount[AbstractEvaluation::STATUS_FAILED]) {
            // if there is one failed resource the workspace is considered as failed also
            $status = AbstractEvaluation::STATUS_FAILED;
        } elseif ($progression === $progressionMax) {
            // if all resources have been done without failure the workspace is completed
            $status = AbstractEvaluation::STATUS_COMPLETED;
        }

        $evaluation->setProgressionMax($progressionMax);
        $evaluation->setProgression($progression);
        $evaluation->setStatus($status);

        if ($date) {
            $evaluation->setDate($date);
        }

        if ($scoreMax) {
            $evaluation->setScore($score);
            $evaluation->setScoreMax($scoreMax);
        }

        $this->om->persist($evaluation);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new UserEvaluationEvent($evaluation), 'workspace.evaluate');

        return $evaluation;
    }

    /**
     * Create requirements for a list of roles in a workspace.
     *
     * @return array
     */
    public function createRolesRequirements(Workspace $workspace, array $roles, array $resources = [])
    {
        $createdRequirements = [];

        foreach ($roles as $role) {
            $requirements = $this->requirementsRepo->findOneBy(['workspace' => $workspace, 'role' => $role]);

            if (!$requirements) {
                $requirements = new Requirements();
                $requirements->setWorkspace($workspace);
                $requirements->setRole($role);
                $this->om->persist($requirements);
                $createdRequirements[] = $requirements;
            }
            foreach ($resources as $resource) {
                $requirements->addResource($resource);

                // Creates corresponding resource evaluation and sets required flag to true
                $this->addRequirementToResourceEvaluationByRole($resource, $role);
            }
        }
        $this->om->flush();

        return $createdRequirements;
    }

    /**
     * Create requirements for a list of users in a workspace.
     *
     * @return array
     */
    public function createUsersRequirements(Workspace $workspace, array $users, array $resources = [])
    {
        $createdRequirements = [];

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $requirements = $this->requirementsRepo->findOneBy(['workspace' => $workspace, 'user' => $user]);

            if (!$requirements) {
                $requirements = new Requirements();
                $requirements->setWorkspace($workspace);
                $requirements->setUser($user);
                $this->om->persist($requirements);
                $createdRequirements[] = $requirements;
            }
            foreach ($resources as $resource) {
                $requirements->addResource($resource);

                // Creates corresponding resource evaluation and sets required flag to true
                $this->addRequirementToResourceEvaluation($resource, $user);
            }
        }
        $this->om->endFlushSuite();

        return $createdRequirements;
    }

    /**
     * Delete a list of requirements.
     */
    public function deleteMultipleRequirements(array $multipleRequirements)
    {
        $this->om->startFlushSuite();

        foreach ($multipleRequirements as $requirements) {
            // Sets required flag of resource evaluation to false
            $user = $requirements->getUser();
            $role = $requirements->getRole();

            if ($user) {
                foreach ($requirements->getResources()->toArray() as $resource) {
                    $this->removeRequirementFromResourceEvaluation($resource, $user);
                }
            }
            if ($role) {
                foreach ($requirements->getResources()->toArray() as $resource) {
                    $this->removeRequirementFromResourceEvaluationByRole($resource, $role);
                }
            }

            $this->om->remove($requirements);
        }
        $this->om->endFlushSuite();
    }

    /**
     * Add a list of resources to a Requirements entity.
     *
     * @return Requirements
     */
    public function addResourcesToRequirements(Requirements $requirements, array $resourceNodes)
    {
        $this->om->startFlushSuite();

        $user = $requirements->getUser();
        $role = $requirements->getRole();

        foreach ($resourceNodes as $resourceNode) {
            if ('directory' === $resourceNode->getResourceType()->getName()) {
                $this->addResourcesToRequirements($requirements, $resourceNode->getChildren()->toArray());
            } else {
                $requirements->addResource($resourceNode);

                // Creates corresponding resource evaluation and sets required flag to true
                if ($user) {
                    $this->addRequirementToResourceEvaluation($resourceNode, $user);
                }
                if ($role) {
                    $this->addRequirementToResourceEvaluationByRole($resourceNode, $role);
                }
            }
        }
        $this->om->endFlushSuite();

        return $requirements;
    }

    /**
     * Remove a list of resources from a Requirements entity.
     *
     * @return Requirements
     */
    public function removeResourcesFromRequirements(Requirements $requirements, array $resourceNodes)
    {
        $this->om->startFlushSuite();

        $user = $requirements->getUser();
        $role = $requirements->getRole();

        foreach ($resourceNodes as $resourceNode) {
            $requirements->removeResource($resourceNode);

            // Sets required flag of resource evaluation to false if it is requirements for an user
            if ($user) {
                $this->removeRequirementFromResourceEvaluation($resourceNode, $user);
            }
            if ($role) {
                $this->removeRequirementFromResourceEvaluationByRole($resourceNode, $role);
            }
        }
        $this->om->endFlushSuite();

        return $requirements;
    }

    /**
     * Fetch all requirements associated to a role and update (add/remove) a list of users for all of them.
     *
     * @param string $type
     */
    public function manageRoleSubscription(Role $role, array $users, $type = 'add')
    {
        $roleRequirements = $this->requirementsRepo->findBy(['role' => $role]);

        $this->om->startFlushSuite();

        foreach ($roleRequirements as $requirements) {
            foreach ($requirements->getResources()->toArray() as $resourceNode) {
                foreach ($users as $user) {
                    switch ($type) {
                        case 'add':
                            $this->addRequirementToResourceEvaluation($resourceNode, $user);
                            break;
                        case 'remove':
                            $this->removeRequirementFromResourceEvaluation($resourceNode, $user);
                            break;
                    }
                }
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * Fetch all requirements associated to each role of a group and update (add/remove) a list of users for all of them.
     *
     * @param string $type
     */
    public function manageGroupSubscription(Group $group, array $users, $type = 'add')
    {
        $roles = $group->getEntityRoles();

        foreach ($roles as $role) {
            $this->manageRoleSubscription($role, $users, $type);
        }
    }

    /**
     * Set required flag to true for each resource evaluations linked to the users having the given role.
     */
    public function addRequirementToResourceEvaluationByRole(ResourceNode $resourceNode, Role $role)
    {
        $users = [];

        foreach ($role->getUsers() as $user) {
            $users[$user->getUuid()] = $user;
        }
        foreach ($role->getGroups() as $group) {
            foreach ($group->getUsers() as $user) {
                $users[$user->getUuid()] = $user;
            }
        }

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->addRequirementToResourceEvaluation($resourceNode, $user);
        }

        $this->om->endFlushSuite();
    }

    /**
     * Set required flag to false for each resource evaluations linked to the users having the given role.
     */
    private function removeRequirementFromResourceEvaluationByRole(ResourceNode $resourceNode, Role $role)
    {
        $users = [];

        foreach ($role->getUsers() as $user) {
            $users[$user->getUuid()] = $user;
        }
        foreach ($role->getGroups() as $group) {
            foreach ($group->getUsers() as $user) {
                $users[$user->getUuid()] = $user;
            }
        }

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $this->removeRequirementFromResourceEvaluation($resourceNode, $user);
        }

        $this->om->endFlushSuite();
    }

    /**
     * Set required flag to true for resource evaluation linked to an user.
     * Resource evaluation is created if it doesn't exist.
     */
    private function addRequirementToResourceEvaluation(ResourceNode $resourceNode, User $user)
    {
        $resourceUserEval = $this->resourceUserEvalRepo->findOneBy(['resourceNode' => $resourceNode, 'user' => $user]);

        if (!$resourceUserEval) {
            $resourceUserEval = new ResourceUserEvaluation();
            $resourceUserEval->setResourceNode($resourceNode);
            $resourceUserEval->setUser($user);
            $resourceUserEval->setStatus(AbstractEvaluation::STATUS_TODO);
        }
        $resourceUserEval->setRequired(true);
        $this->om->persist($resourceUserEval);
        $this->om->flush();
    }

    /**
     * Set required flag to false for resource evaluation linked to an user.
     */
    private function removeRequirementFromResourceEvaluation(ResourceNode $resourceNode, User $user)
    {
        $resourceUserEval = $this->resourceUserEvalRepo->findOneBy(['resourceNode' => $resourceNode, 'user' => $user]);

        if ($resourceUserEval) {
            $resourceUserEval->setRequired(false);
            $this->om->persist($resourceUserEval);
        }
        $this->om->flush();
    }

    /**
     * Add duration to a workspace user evaluation.
     *
     * @param int $duration
     */
    public function addDurationToWorkspaceEvaluation(Workspace $workspace, User $user, $duration)
    {
        $this->om->startFlushSuite();

        $workspaceEval = $this->getEvaluation($workspace, $user);

        $evaluationDuration = $workspaceEval->getDuration();
        if (is_null($workspaceEval->getDuration())) {
            $evaluationDuration = $this->computeDuration($workspaceEval);
        }

        $workspaceEval->setDuration($evaluationDuration + $duration);

        $this->om->persist($workspaceEval);
        $this->om->flush();

        $this->om->endFlushSuite();
    }

    /**
     * Compute duration for a workspace user evaluation.
     *
     * @return int
     */
    public function computeDuration(Evaluation $workspaceEvaluation)
    {
        /** @var LogConnectWorkspace[] $workspaceLogs */
        $workspaceLogs = $this->logConnectRepo->findBy(['workspace' => $workspaceEvaluation->getWorkspace(), 'user' => $workspaceEvaluation->getUser()]);

        $duration = 0;
        foreach ($workspaceLogs as $log) {
            if ($log->getDuration()) {
                $duration += $log->getDuration();
            }
        }

        $workspaceEvaluation->setDuration($duration);

        $this->om->persist($workspaceEvaluation);
        $this->om->flush();

        return $duration;
    }
}
