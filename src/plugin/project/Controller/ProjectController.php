<?php

namespace Claroline\ProjectBundle\Controller;

use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\ProjectBundle\Entity\Project;
use Claroline\ProjectBundle\Manager\ProjectManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/project')]
class ProjectController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ProjectManager $projectManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Returns statistics for the trainer dashboard.
     */
    #[Route(path: '/{id}/stats', name: 'claro_project_stats', methods: ['GET'])]
    public function statsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Project $project
    ): JsonResponse {
        $this->checkPermission('EDIT', $project->getResourceNode(), [], true);

        return new JsonResponse($this->projectManager->getStats($project));
    }
}
