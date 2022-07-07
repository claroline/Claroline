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
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\InitializeWorkspaceEvaluations;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/evaluations/workspace")
 */
class WorkspaceEvaluationController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TranslatorInterface */
    private $translator;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var FinderProvider */
    private $finder;
    /** @var SerializerProvider */
    private $serializer;
    /** @var WorkspaceEvaluationManager */
    private $manager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        TranslatorInterface $translator,
        MessageBusInterface $messageBus,
        ObjectManager $om,
        Crud $crud,
        FinderProvider $finder,
        SerializerProvider $serializer,
        WorkspaceEvaluationManager $manager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->translator = $translator;
        $this->messageBus = $messageBus;
        $this->om = $om;
        $this->crud = $crud;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * @Route("/", name="apiv2_workspace_evaluations_all", methods={"GET"})
     */
    public function listAction(Request $request): JsonResponse
    {
        $this->checkToolAccess('OPEN');

        $hiddenFilters = [];

        // don't show all users evaluations if no right
        if (!$this->checkToolAccess('SHOW_EVALUATIONS', null, false)) {
            $hiddenFilters['user'] = $this->tokenStorage->getToken()->getUser()->getUuid();
        }

        return new JsonResponse($this->crud->list(
            Evaluation::class,
            array_merge($request->query->all(), ['hiddenFilters' => $hiddenFilters])
        ));
    }

    /**
     * @Route("/{workspace}", name="apiv2_workspace_evaluations_list", methods={"GET"})
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listByWorkspaceAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkToolAccess('OPEN', $workspace);

        $hiddenFilters = [
            'workspace' => $workspace->getUuid(),
        ];

        // don't show all users evaluations if no right
        if (!$this->checkToolAccess('SHOW_EVALUATIONS', $workspace, false)) {
            $hiddenFilters['user'] = $this->tokenStorage->getToken()->getUser()->getUuid();
        }

        return new JsonResponse($this->crud->list(
            Evaluation::class,
            array_merge($request->query->all(), ['hiddenFilters' => $hiddenFilters])
        ));
    }

    /**
     * @Route("/csv/{workspaceId}", name="apiv2_workspace_evaluation_csv", methods={"GET"})
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function exportAction(Request $request, ?string $workspaceId = null): BinaryFileResponse
    {
        $workspace = null;
        if (!empty($workspaceId)) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $workspaceId]);
            if (empty($workspace)) {
                throw new NotFoundHttpException('Workspace not found.');
            }
        }

        $this->checkToolAccess('EDIT', $workspace);

        $query = $request->query->all();
        // remove pagination if any
        $query['page'] = 0;
        $query['limit'] = -1;
        $query['columns'] = [
            'user.lastName',
            'user.firstName',
            'user.username',
            'user.email',
            'date',
            'status',
            'progression',
            'progressionMax',
            'score',
            'scoreMax',
            'duration',
        ];

        if (!isset($query['filters'])) {
            $query['filters'] = [];
        }

        if (!empty($workspace)) {
            $query['filters']['workspace'] = $workspace->getUuid();
        } else {
            // adds the workspace names in the export
            array_unshift($query['columns'], 'workspace.name');
        }

        $csvFilename = $this->crud->csv(Evaluation::class, $query);

        $now = new \DateTime();
        $fileName = "workspace-evaluations{$now->format('Y-m-d-His')}.csv";

        return new BinaryFileResponse($csvFilename, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ]);
    }

    /**
     * @Route("/{workspace}/init", name="apiv2_workspace_evaluations_init", methods={"PUT"})
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function initializeAction(Workspace $workspace): JsonResponse
    {
        $this->checkToolAccess('EDIT', $workspace);

        $users = $this->om->getRepository(User::class)->findByWorkspaces([$workspace]);
        if (!empty($users)) {
            $this->messageBus->dispatch(
                new InitializeWorkspaceEvaluations($workspace->getId(), array_map(function (User $user) {
                    return $user->getId();
                }, $users))
            );
        }

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{workspace}/progression/{user}", name="apiv2_workspace_get_user_progression", methods={"GET"})
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
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
     * @Route("/{workspace}/progression/{user}/export", name="apiv2_workspace_export_user_progression", methods={"GET"})
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function exportUserProgressionAction(Workspace $workspace, User $user): StreamedResponse
    {
        /** @var Evaluation $workspaceEvaluation */
        $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user,
        ]);

        if (empty($workspaceEvaluation)) {
            throw new NotFoundHttpException();
        }

        $this->checkPermission('OPEN', $workspaceEvaluation, [], true);

        /** @var ResourceUserEvaluation[] $resourceUserEvaluations */
        $resourceUserEvaluations = $this->finder->searchEntities(ResourceUserEvaluation::class, [
            'filters' => ['workspace' => $workspace->getUuid(), 'user' => $user->getUuid()],
            'sortBy' => '-date',
        ])['data'];

        return new StreamedResponse(function () use ($workspace, $workspaceEvaluation, $resourceUserEvaluations) {
            // Prepare CSV file
            $handle = fopen('php://output', 'w+');

            // Create header
            fputcsv($handle, [
                $this->translator->trans('name', [], 'platform'),
                $this->translator->trans('type', [], 'platform'),
                $this->translator->trans('date', [], 'platform'),
                $this->translator->trans('status', [], 'platform'),
                $this->translator->trans('progression', [], 'platform'),
                $this->translator->trans('progressionMax', [], 'platform'),
                $this->translator->trans('score', [], 'platform'),
                $this->translator->trans('score_total', [], 'platform'),
                $this->translator->trans('duration', [], 'platform'),
            ], ';', '"');

            // put Workspace evaluation
            fputcsv($handle, [
                $workspace->getName(),
                $this->translator->trans('workspace', [], 'platform'),
                DateNormalizer::normalize($workspaceEvaluation->getDate()),
                $workspaceEvaluation->getStatus(),
                $workspaceEvaluation->getProgression(),
                $workspaceEvaluation->getProgressionMax(),
                $workspaceEvaluation->getScore(),
                $workspaceEvaluation->getScoreMax(),
                $workspaceEvaluation->getDuration(),
            ], ';', '"');

            // Get evaluations
            foreach ($resourceUserEvaluations as $resourceUserEvaluation) {
                // put ResourceUserEvaluation
                fputcsv($handle, [
                    $resourceUserEvaluation->getResourceNode()->getName(),
                    $this->translator->trans('resource', [], 'platform'),
                    DateNormalizer::normalize($resourceUserEvaluation->getDate()),
                    $resourceUserEvaluation->getStatus(),
                    $resourceUserEvaluation->getProgression(),
                    $resourceUserEvaluation->getProgressionMax(),
                    $resourceUserEvaluation->getScore(),
                    $resourceUserEvaluation->getScoreMax(),
                    $resourceUserEvaluation->getDuration(),
                ], ';', '"');

                /** @var ResourceEvaluation[] $resourceEvaluations */
                $resourceEvaluations = $this->finder->searchEntities(ResourceEvaluation::class, [
                    'filters' => ['resourceUserEvaluation' => $resourceUserEvaluation],
                    'sortBy' => '-date',
                ])['data'];

                foreach ($resourceEvaluations as $resourceEvaluation) {
                    fputcsv($handle, [
                        $resourceUserEvaluation->getResourceNode()->getName(),
                        $this->translator->trans('attempt', [], 'platform'),
                        DateNormalizer::normalize($resourceEvaluation->getDate()),
                        $resourceEvaluation->getStatus(),
                        $resourceEvaluation->getProgression(),
                        $resourceEvaluation->getProgressionMax(),
                        $resourceEvaluation->getScore(),
                        $resourceEvaluation->getScoreMax(),
                        $resourceEvaluation->getDuration(),
                    ], ';', '"');
                }
            }

            fclose($handle);

            return $handle;
        }, 200, [
            'Content-Type' => 'application/force-download',
            'Content-Disposition' => 'attachment; filename="'.TextNormalizer::toKey("progression-{$user->getFullName()}").'.csv"',
        ]);
    }

    /**
     * @Route("/{workspace}/requirements", name="apiv2_workspace_required_resource_list", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function listRequiredResourcesAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkToolAccess('OPEN', $workspace);

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, array_merge($request->query->all(), ['hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
                'required' => true,
            ]]), [Options::SERIALIZE_MINIMAL])
        );
    }

    /**
     * @Route("/{workspace}/requirements", name="apiv2_workspace_required_resource_add", methods={"PATCH"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function addRequiredResourcesAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkToolAccess('EDIT', $workspace);

        $resources = $this->decodeIdsString($request, ResourceNode::class);

        $this->om->startFlushSuite();
        foreach ($resources as $resource) {
            $this->crud->update($resource, [
                'id' => $resource->getUuid(),
                'evaluation' => ['required' => true],
            ], [Crud::NO_PERMISSIONS]);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{workspace}/requirements", name="apiv2_workspace_required_resource_remove", methods={"DELETE"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function removeRequiredResourcesAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkToolAccess('EDIT', $workspace);

        $resources = $this->decodeIdsString($request, ResourceNode::class);

        $this->om->startFlushSuite();
        foreach ($resources as $resource) {
            $this->crud->update($resource, [
                'id' => $resource->getUuid(),
                'evaluation' => ['required' => false],
            ], [Crud::NO_PERMISSIONS]);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    /**
     * Checks user rights to access evaluation tool.
     */
    private function checkToolAccess(string $permission, ?Workspace $workspace = null, bool $exception = true): bool
    {
        if (!empty($workspace)) {
            $evaluationTool = $this->om->getRepository(OrderedTool::class)->findOneByNameAndWorkspace('evaluation', $workspace);
        } else {
            $evaluationTool = $this->om->getRepository(OrderedTool::class)->findOneByNameAndDesktop('evaluation');
        }

        if ($this->authorization->isGranted($permission, $evaluationTool)) {
            return true;
        }

        if ($exception) {
            throw new AccessDeniedException();
        }

        return false;
    }
}
