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
use Claroline\CoreBundle\Entity\AbstractEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Requirements;
use Claroline\CoreBundle\Entity\Workspace\Workspace;

class EvaluationManager
{
    /** @var ObjectManager */
    private $om;

    private $evaluationRepo;
    private $requirementsRepo;
    private $resourceUserEvalRepo;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->evaluationRepo = $om->getRepository(Evaluation::class);
        $this->requirementsRepo = $om->getRepository(Requirements::class);
        $this->resourceUserEvalRepo = $om->getRepository(ResourceUserEvaluation::class);
    }

    /**
     * @param Workspace $workspace
     * @param User      $user
     * @param bool      $withCreation
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
            $evaluation->setStatus(AbstractEvaluation::STATUS_OPENED);
            $this->om->persist($evaluation);
            $this->om->flush();
        }

        return $evaluation;
    }

    /**
     * @param Workspace $workspace
     * @param User      $user
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
        $userRequirements = $this->requirementsRepo->findOneBy(['workspace' => $workspace, 'user' => $user]);

        if ($userRequirements) {
            foreach ($userRequirements->getResources() as $node) {
                $resources[$node->getUuid()] = $node;
            }
        }

        // Retrieves resources that have to be done by the roles in the workspace
        foreach ($roles as $role) {
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
     * @param Workspace                   $workspace
     * @param User                        $user
     * @param ResourceUserEvaluation|null $currentRue
     *
     * @return Evaluation|null
     *
     * @throws \Exception
     */
    public function computeEvaluation(Workspace $workspace, User $user, ResourceUserEvaluation $currentRue = null)
    {
        $evaluation = $this->getEvaluation($workspace, $user);

        // no computation if workspace has already been passed or completed
        if (!in_array($evaluation->getStatus(), [AbstractEvaluation::STATUS_PASSED, AbstractEvaluation::STATUS_COMPLETED])) {
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

            $progressionMax = count($resources);

            // if there is a triggering resource evaluation checks if is part of the workspace requirements
            // if not, no evalution is computed
            if ($currentRue) {
                $currentResourceId = $currentRue->getResourceNode()->getUuid();

                if (isset($resources[$currentResourceId])) {
                    if ($currentRue->getStatus()) {
                        ++$statusCount[$currentRue->getStatus()];
                    }
                    unset($resources[$currentResourceId]);
                } else {
                    return $evaluation;
                }
            }

            foreach ($resources as $resource) {
                $resourceEval = $this->resourceUserEvalRepo->findOneBy(['resourceNode' => $resource, 'user' => $user]);

                if ($resourceEval && $resourceEval->getStatus()) {
                    ++$statusCount[$resourceEval->getStatus()];
                }
            }

            $progression = $statusCount[AbstractEvaluation::STATUS_PASSED] +
                $statusCount[AbstractEvaluation::STATUS_FAILED] +
                $statusCount[AbstractEvaluation::STATUS_COMPLETED] +
                $statusCount[AbstractEvaluation::STATUS_OPENED] +
                $statusCount[AbstractEvaluation::STATUS_PARTICIPATED];

            $status = AbstractEvaluation::STATUS_INCOMPLETE;

            if (0 < count($statusCount[AbstractEvaluation::STATUS_FAILED])) {
                // if there is one failed resource the workspace is considered as failed also
                $status = AbstractEvaluation::STATUS_FAILED;
            } elseif ($progression === $progressionMax) {
                // if all resources have been done without failure the workspace is completed
                $status = AbstractEvaluation::STATUS_COMPLETED;
            }

            $evaluation->setProgressionMax($progressionMax);
            $evaluation->setProgression($progression);
            $evaluation->setStatus($status);
            $evaluation->setDate(new \DateTime());
            $this->om->persist($evaluation);
            $this->om->flush();
        }

        return $evaluation;
    }

    /**
     * @param Workspace $workspace
     * @param array     $roles
     * @param array     $resources
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
            }
        }
        $this->om->flush();

        return $createdRequirements;
    }

    /**
     * @param Workspace $workspace
     * @param array     $users
     * @param array     $resources
     *
     * @return array
     */
    public function createUsersRequirements(Workspace $workspace, array $users, array $resources = [])
    {
        $createdRequirements = [];

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
            }
        }
        $this->om->flush();

        return $createdRequirements;
    }

    /**
     * @param array $multipleRequirements
     */
    public function deleteMultipleRequirements(array $multipleRequirements)
    {
        foreach ($multipleRequirements as $requirements) {
            $this->om->remove($requirements);
        }
        $this->om->flush();
    }

    /**
     * @param Requirements $requirements
     * @param array        $resourceNodes
     *
     * @return Requirements
     */
    public function addResourcesToRequirements(Requirements $requirements, array $resourceNodes)
    {
        $this->om->startFlushSuite();

        foreach ($resourceNodes as $resourceNode) {
            if ('directory' === $resourceNode->getResourceType()->getName()) {
                $this->addResourcesToRequirements($requirements, $resourceNode->getChildren()->toArray());
            } else {
                $requirements->addResource($resourceNode);
            }
        }
        $this->om->endFlushSuite();

        return $requirements;
    }

    /**
     * @param Requirements $requirements
     * @param array        $resourceNodes
     *
     * @return Requirements
     */
    public function removeResourcesFromRequirements(Requirements $requirements, array $resourceNodes)
    {
        foreach ($resourceNodes as $resourceNode) {
            $requirements->removeResource($resourceNode);
        }
        $this->om->flush();

        return $requirements;
    }
}
