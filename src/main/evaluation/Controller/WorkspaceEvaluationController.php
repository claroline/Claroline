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
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Manager\File\ArchiveManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\EvaluationBundle\Manager\PdfManager;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    /** @var PdfManager */
    private $pdfManager;
    private $archiveManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        TranslatorInterface $translator,
        ObjectManager $om,
        Crud $crud,
        FinderProvider $finder,
        SerializerProvider $serializer,
        WorkspaceEvaluationManager $manager,
        PdfManager $pdfManager,
        ArchiveManager $archiveManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->translator = $translator;
        $this->om = $om;
        $this->crud = $crud;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->manager = $manager;
        $this->pdfManager = $pdfManager;
        $this->archiveManager = $archiveManager;
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
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            $hiddenFilters['user'] = $user->getUuid();
        }

        return new JsonResponse($this->crud->list(
            Evaluation::class,
            array_merge($request->query->all(), ['hiddenFilters' => $hiddenFilters])
        ));
    }

    /**
     * @Route("/{workspace}", name="apiv2_workspace_evaluations_list", methods={"GET"})
     *
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
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            $hiddenFilters['user'] = $user->getUuid();
        }

        return new JsonResponse($this->crud->list(
            Evaluation::class,
            array_merge($request->query->all(), ['hiddenFilters' => $hiddenFilters])
        ));
    }

    /**
     * @Route("/{workspace}/user/{user}", name="apiv2_workspace_evaluation_get", methods={"GET"})
     *
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function getAction(Workspace $workspace, User $user): JsonResponse
    {
        if (!$this->checkToolAccess('SHOW_EVALUATIONS', $workspace, false)
            && (!$this->tokenStorage->getToken()->getUser() instanceof User || $user->getUuid() !== $this->tokenStorage->getToken()->getUser()->getUuid())
        ) {
            throw new AccessDeniedException();
        }

        $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user,
        ]);

        return new JsonResponse(
            $this->serializer->serialize($workspaceEvaluation)
        );
    }

    /**
     * @Route("/{workspace}/init", name="apiv2_workspace_evaluations_init", methods={"PUT"})
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function initializeAction(Workspace $workspace): JsonResponse
    {
        $this->checkToolAccess('EDIT', $workspace);

        $this->manager->initialize($workspace);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{workspace}/recompute", name="apiv2_workspace_evaluations_recompute", methods={"PUT"})
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function recomputeAction(Workspace $workspace): JsonResponse
    {
        $this->checkToolAccess('EDIT', $workspace);

        $this->manager->recompute($workspace);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{workspace}/progression/{user}", name="apiv2_workspace_get_user_progression", methods={"GET"})
     *
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
     * @Route("/{workspace}/certificate/{user}/participation", name="apiv2_workspace_download_participation_certificate", methods={"GET"})
     *
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function downloadParticipationCertificateAction(Workspace $workspace, User $user): StreamedResponse
    {
        $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user,
        ]);

        if (empty($workspaceEvaluation)) {
            throw new NotFoundHttpException('Workspace evaluation not found.');
        }

        $this->checkPermission('OPEN', $workspaceEvaluation, [], true);

        $certificate = $this->pdfManager->getWorkspaceParticipationCertificate($workspaceEvaluation);
        if (empty($certificate)) {
            throw new NotFoundHttpException('No participation certificate is available yet.');
        }

        return new StreamedResponse(function () use ($certificate) {
            echo $certificate;
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($workspace->getName()).'-'.TextNormalizer::toKey($user->getFullName()).'-participation.pdf',
        ]);
    }

    /**
     * @Route("/{workspace}/certificate/{user}/success", name="apiv2_workspace_download_success_certificate", methods={"GET"})
     *
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function downloadSuccessCertificateAction(Workspace $workspace, User $user): StreamedResponse
    {
        $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user,
        ]);

        if (empty($workspaceEvaluation)) {
            throw new NotFoundHttpException('Workspace evaluation not found.');
        }

        $this->checkPermission('OPEN', $workspaceEvaluation, [], true);

        $certificate = $this->pdfManager->getWorkspaceSuccessCertificate($workspaceEvaluation);
        if (empty($certificate)) {
            throw new NotFoundHttpException('No success certificate is available yet.');
        }

        return new StreamedResponse(function () use ($certificate) {
            echo $certificate;
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($workspace->getName()).'-'.TextNormalizer::toKey($user->getFullName()).'-success.pdf',
        ]);
    }

    /**
     * @Route("/certificates/participation", name="apiv2_workspace_download_participation_certificates", methods={"POST"})
     */
    public function downloadParticipationCertificatesAction(Request $request): StreamedResponse
    {
        $workspaceEvaluationsIds = $this->decodeRequest($request);
        if (empty($workspaceEvaluationsIds)) {
            throw new NotFoundHttpException('No workspace evaluations ids found in request body.');
        }

        foreach ($workspaceEvaluationsIds as $workspaceEvaluationId) {
            $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
                'uuid' => $workspaceEvaluationId,
            ]);

            if (empty($workspaceEvaluation)) {
                throw new NotFoundHttpException('Workspace evaluation not found.');
            }

            $workspace = $workspaceEvaluation->getWorkspace();
            $user = $workspaceEvaluation->getUser();

            if (1 === count($workspaceEvaluationsIds)) {
                return $this->downloadParticipationCertificateAction($workspace, $user);
            }

            if (!isset($archive)) {
                $archive = $this->archiveManager->create(null, new FileBag());
                $archivePath = $archive->filename;
            }

            $this->checkPermission('OPEN', $workspaceEvaluation, [], true);

            $certificate = $this->pdfManager->getWorkspaceParticipationCertificate($workspaceEvaluation);
            if (!empty($certificate)) {
                $archive->addFromString($workspace->getName().'-'.TextNormalizer::toKey($user->getFullName()).'-participation.pdf', $certificate);
            }
        }

        if (!isset($archive) || !isset($archivePath) || 0 === $archive->numFiles) {
            throw new NotFoundHttpException('No participation certificates are available yet.');
        }

        $archive->close();

        return new StreamedResponse(function () use ($archivePath) {
            readfile($archivePath);
        }, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename=participations.zip',
        ]);
    }

    /**
     * @Route("/certificates/success", name="apiv2_workspace_download_success_certificates", methods={"POST"})
     */
    public function downloadSuccessCertificatesAction(Request $request): StreamedResponse
    {
        $workspaceEvaluationsIds = $this->decodeRequest($request);
        if (empty($workspaceEvaluationsIds)) {
            throw new NotFoundHttpException('No workspace evaluations ids found in request body.');
        }

        foreach ($workspaceEvaluationsIds as $workspaceEvaluationId) {
            $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
                'uuid' => $workspaceEvaluationId,
            ]);

            if (empty($workspaceEvaluation)) {
                throw new NotFoundHttpException('Workspace evaluation not found.');
            }

            $workspace = $workspaceEvaluation->getWorkspace();
            $user = $workspaceEvaluation->getUser();

            if (1 === count($workspaceEvaluationsIds)) {
                return $this->downloadSuccessCertificateAction($workspace, $user);
            }

            if (!isset($archive)) {
                $archive = $this->archiveManager->create(null, new FileBag());
                $archivePath = $archive->filename;
            }

            $this->checkPermission('OPEN', $workspaceEvaluation, [], true);

            $certificate = $this->pdfManager->getWorkspaceSuccessCertificate($workspaceEvaluation);
            if (!empty($certificate)) {
                $archive->addFromString($workspace->getName().'-'.TextNormalizer::toKey($user->getFullName()).'-success.pdf', $certificate);
            }
        }

        if (!isset($archive) || !isset($archivePath) || 0 === $archive->numFiles) {
            throw new NotFoundHttpException('No success certificates are available yet.');
        }

        $archive->close();

        return new StreamedResponse(function () use ($archivePath) {
            readfile($archivePath);
        }, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename=participations.zip',
        ]);
    }

    /**
     * @Route("/{workspace}/requirements", name="apiv2_workspace_required_resource_list", methods={"GET"})
     *
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
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
     * @Route("/{workspace}/requirements", name="apiv2_workspace_required_resource_add", methods={"PATCH"})
     *
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
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
     * @Route("/{workspace}/requirements", name="apiv2_workspace_required_resource_remove", methods={"DELETE"})
     *
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function removeRequiredResourcesAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkToolAccess('EDIT', $workspace);

        $resources = $this->decodeIdsString($request, ResourceNode::class);

        // we can not do it inside a flush suite because it will trigger the Workspace to recompute its evaluation
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
