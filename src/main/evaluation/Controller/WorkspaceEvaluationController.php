<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/evaluations/workspace')]
class WorkspaceEvaluationController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly FinderProvider $finder,
        private readonly SerializerProvider $serializer,
        private readonly WorkspaceEvaluationManager $manager
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/', name: 'apiv2_workspace_evaluations_all', methods: ['GET'])]
    public function listAction(Request $request): JsonResponse
    {
        $this->checkToolAccess('OPEN');

        return new JsonResponse($this->crud->list(
            Evaluation::class,
            $request->query->all()
        ));
    }

    /**
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    #[Route(path: '/{workspace}', name: 'apiv2_workspace_evaluations_list', methods: ['GET'])]
    public function listByWorkspaceAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkToolAccess('OPEN', $workspace);

        return new JsonResponse($this->crud->list(
            Evaluation::class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
            ]])
        ));
    }

    /**
     *
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    #[Route(path: '/{workspace}/user/{user}', name: 'apiv2_workspace_evaluation_get', methods: ['GET'])]
    public function getAction(Workspace $workspace, User $user): JsonResponse
    {
        $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user,
        ]);

        $this->checkPermission('OPEN', $workspaceEvaluation, [], true);

        return new JsonResponse(
            $this->serializer->serialize($workspaceEvaluation)
        );
    }

    /**
     * Initializes evaluations for all the users of a workspace.
     *
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    #[Route(path: '/{workspace}/init', name: 'apiv2_workspace_evaluations_init', methods: ['PUT'])]
    public function initializeAction(Workspace $workspace): JsonResponse
    {
        $this->checkToolAccess('EDIT', $workspace);

        $this->manager->initialize($workspace);

        return new JsonResponse(null, 204);
    }

    /**
     * Recalculates (score, status, progression, ...) evaluations for all the users of a workspace.
     *
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    #[Route(path: '/{workspace}/recompute', name: 'apiv2_workspace_evaluations_recompute', methods: ['PUT'])]
    public function recomputeAction(Workspace $workspace): JsonResponse
    {
        $this->checkToolAccess('EDIT', $workspace);

        $this->manager->recompute($workspace);

        return new JsonResponse(null, 204);
    }

    /**
     *
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    #[Route(path: '/{workspace}/progression/{user}', name: 'apiv2_workspace_get_user_progression', methods: ['GET'])]
    public function getUserProgressionAction(Workspace $workspace, User $user): JsonResponse
    {
        $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user,
        ]);

        if (empty($workspaceEvaluation)) {
            throw new NotFoundHttpException();
        }

        $this->checkPermission('OPEN', $workspaceEvaluation, [], true);

        return new JsonResponse([
            'workspaceEvaluation' => $this->serializer->serialize($workspaceEvaluation),
            'resourceEvaluations' => $this->finder->search(ResourceUserEvaluation::class, [
                'filters' => ['workspace' => $workspace->getUuid(), 'user' => $user->getUuid()],
            ])['data'],
        ]);
    }

    /**
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    #[Route(path: '/{workspace}/requirements', name: 'apiv2_workspace_required_resource_list', methods: ['GET'])]
    public function listRequiredResourcesAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkToolAccess('OPEN', $workspace);

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, array_merge($request->query->all(), ['hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
                'required' => true,
            ]]), [Options::SERIALIZE_LIST])
        );
    }

    /**
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    #[Route(path: '/{workspace}/requirements', name: 'apiv2_workspace_required_resource_add', methods: ['PATCH'])]
    public function addRequiredResourcesAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkToolAccess('EDIT', $workspace);

        $resources = $this->decodeIdsString($request, ResourceNode::class);

        // we can not do it inside a flush suite because it will trigger the Workspace to recompute its evaluation
        // and it requires to have all the data recorded inside the db.
        // we can create a messenger message for it later if there are performances issues.
        foreach ($resources as $resource) {
            $this->crud->update($resource, [
                'id' => $resource->getUuid(),
                'evaluation' => ['required' => true],
            ], [Crud::NO_PERMISSIONS]);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    #[Route(path: '/{workspace}/requirements', name: 'apiv2_workspace_required_resource_remove', methods: ['DELETE'])]
    public function removeRequiredResourcesAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkToolAccess('EDIT', $workspace);

        $resources = $this->decodeIdsString($request, ResourceNode::class);

        // we can not do it inside a flush suite because it will trigger the Workspace to recompute its evaluation,
        // and it requires to have all the data recorded inside the db.
        // we can create a messenger message for it later if there are performances issues.
        foreach ($resources as $resource) {
            $this->crud->update($resource, [
                'id' => $resource->getUuid(),
                'evaluation' => ['required' => false],
            ], [Crud::NO_PERMISSIONS]);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Checks user rights to access evaluation tool.
     */
    private function checkToolAccess(string $permission, Workspace $workspace = null, bool $exception = true): bool
    {
        if (!empty($workspace)) {
            $evaluationTool = $this->om->getRepository(OrderedTool::class)->findOneByNameAndContext('evaluation', WorkspaceContext::getName(), $workspace->getUuid());
        } else {
            $evaluationTool = $this->om->getRepository(OrderedTool::class)->findOneByNameAndContext('evaluation', DesktopContext::getName());
        }

        return $this->checkPermission($permission, $evaluationTool, [], $exception);
    }
}
