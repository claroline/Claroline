<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Workspace;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Controller\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\Model\HasRolesTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceRestrictionsManager;
use Claroline\CoreBundle\Messenger\Message\CopyWorkspace;
use Claroline\CoreBundle\Messenger\Message\CreateWorkspace;
use Claroline\CoreBundle\Messenger\Message\ImportWorkspace;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/workspace", name="apiv2_workspace_")
 */
class WorkspaceController extends AbstractCrudController
{
    use HasGroupsTrait; // to remove : only the list endpoint is used
    use HasRolesTrait;
    use PermissionCheckerTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        private readonly MessageBusInterface $messageBus,
        private readonly TempFileManager $tempManager,
        private readonly RoleManager $roleManager,
        private readonly WorkspaceManager $workspaceManager,
        private readonly WorkspaceRestrictionsManager $restrictionsManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'workspace';
    }

    public static function getClass(): string
    {
        return Workspace::class;
    }

    /**
     * @ApiDoc(
     *     description="The list of registerable workspaces for the current security token.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     *
     * @Route("/list/registerable", name="list_registerable", methods={"GET"})
     */
    public function listRegisterableAction(Request $request): JsonResponse
    {
        return new JsonResponse($this->crud->list(
            Workspace::class,
            array_merge($request->query->all(), ['hiddenFilters' => array_merge($this->getDefaultHiddenFilters(), [
                'displayable' => true,
                'model' => false,
                'selfRegistration' => true,
            ])]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @ApiDoc(
     *     description="The list of public workspaces for the current security token.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     *
     * @Route("/list/public", name="list_public", methods={"GET"})
     */
    public function listPublicAction(Request $request): JsonResponse
    {
        return new JsonResponse($this->crud->list(
            Workspace::class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'displayable' => true,
                'model' => false,
                'public' => true,
            ]]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @ApiDoc(
     *     description="The list of registered workspaces for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Workspace\Workspace&!user",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     *
     * @Route("/list/registered", name="list_registered", methods={"GET"})
     */
    public function listRegisteredAction(Request $request): StreamedJsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new StreamedJsonResponse($this->crud->search(
            Workspace::class,
            FinderQuery::fromRequest($request)
                ->addFilters([
                    'model' => false,
                    'roles' => $this->tokenStorage->getToken()->getRoleNames(),
                ]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @ApiDoc(
     *     description="The list of administrated workspaces for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Workspace\Workspace&!administrated",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     *
     * @Route("/list/administrated", name="list_managed", methods={"GET"})
     */
    public function listManagedAction(Request $request): StreamedJsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new StreamedJsonResponse($this->crud->search(
            Workspace::class,
            FinderQuery::fromRequest($request)
                ->addFilters([
                    'model' => false,
                    'administrated' => true,
                ]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @ApiDoc(
     *     description="The list of workspace models for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Workspace\Workspace&!model",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     *
     * @Route("/list/model", name="list_model", methods={"GET"})
     */
    public function listModelAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->crud->list(
            Workspace::class,
            array_merge($request->query->all(), ['hiddenFilters' => array_merge($this->getDefaultHiddenFilters(), [
                'model' => true,
            ])]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @ApiDoc(
     *     description="The list of archived workspace for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Workspace\Workspace&!archived",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     *
     * @Route("/list/archived", name="list_archive", methods={"GET"})
     */
    public function listArchivedAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->crud->list(
            Workspace::class,
            array_merge($request->query->all(), ['hiddenFilters' => array_merge($this->getDefaultHiddenFilters(), [
                'administrated' => true,
                'archived' => true,
            ])]),
            $this->getOptions()['list']
        ));
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->checkPermission('CREATE', new Workspace(), [], true);

        $options = static::getOptions();

        $this->messageBus->dispatch(new CreateWorkspace(
            $this->decodeRequest($request),
            $options['create'] ?? []
        ), [new AuthenticationStamp($this->tokenStorage->getToken()->getUser()->getId())]);

        return new JsonResponse(
            null,
            204
        );
    }

    /**
     * Copies a list of workspaces.
     *
     * @Route("/copy", name="copy", methods={"PUT"})
     */
    public function copyAction(Request $request): JsonResponse
    {
        $options = [Crud::NO_PERMISSIONS];
        if (1 === (int) $request->query->get('model') || 'true' === $request->query->get('model')) {
            $options[] = Options::AS_MODEL;
        }

        $toCopy = $this->decodeIdsString($request, Workspace::class);

        foreach ($toCopy as $workspace) {
            if ($this->checkPermission('COPY', $workspace)) {
                $this->messageBus->dispatch(new CopyWorkspace(
                    $workspace->getId(),
                    $options
                ), [new AuthenticationStamp($this->tokenStorage->getToken()->getUser()->getId())]);
            }
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Creates a new Workspace from a Claroline archive.
     *
     * @Route("/import", name="import", methods={"POST"})
     */
    public function importAction(Request $request): JsonResponse
    {
        $this->checkPermission('CREATE', new Workspace(), [], true);

        $files = $request->files->all();
        if (empty($files)) {
            throw new InvalidDataException('No archive to import.', [['path' => '/archive', 'message' => 'Archive is required']]);
        }

        $archiveFile = array_shift($files);
        $tempPath = $this->tempManager->copy($archiveFile, true);

        $this->messageBus->dispatch(new ImportWorkspace(
            $tempPath,
            !empty($request->request->get('name')) ? $request->request->get('name') : null,
            !empty($request->request->get('code')) ? $request->request->get('code') : null
        ), [new AuthenticationStamp($this->tokenStorage->getToken()->getUser()->getId())]);

        return new JsonResponse(null, 204);
    }

    /**
     * Exports a Workspace into a Claroline archive.
     *
     * @ApiDoc(
     *     description="Export the workspace as a zip archive.",
     *     parameters={
     *         {"name": "id", "type": {"string", "integer"},  "description": "The workspace id or uuid"}
     *     }
     * )
     *
     * @Route("/{id}/export", name="export", methods={"GET"})
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     */
    public function exportAction(Workspace $workspace): BinaryFileResponse
    {
        $this->checkPermission('OPEN', $workspace, [], true);

        $pathArch = $this->workspaceManager->export($workspace);
        $filename = TextNormalizer::toKey($workspace->getCode()).'.zip';

        $response = new BinaryFileResponse($pathArch);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', "attachment; filename={$filename}");

        return $response;
    }

    /**
     * @ApiDoc(
     *     description="Archive workspaces.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of workspace uuids."}
     *     }
     * )
     *
     * @Route("/archive", name="archive", methods={"PUT"})
     */
    public function archiveBulkAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        /** @var Workspace[] $workspaces */
        $workspaces = parent::decodeIdsString($request, Workspace::class);
        foreach ($workspaces as $workspace) {
            if ($this->authorization->isGranted('ADMINISTRATE', $workspace) && !$workspace->isArchived()) {
                $processed[] = $this->workspaceManager->archive($workspace);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $processed));
    }

    /**
     * @ApiDoc(
     *     description="Unarchive workspaces.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of workspace uuids."}
     *     }
     * )
     *
     * @Route("/unarchive", name="unarchive", methods={"PUT"})
     */
    public function unarchiveBulkAction(Request $request): JsonResponse
    {
        $processed = [];

        $this->om->startFlushSuite();

        /** @var Workspace[] $workspaces */
        $workspaces = parent::decodeIdsString($request, Workspace::class);
        foreach ($workspaces as $workspace) {
            if ($this->authorization->isGranted('ADMINISTRATE', $workspace) && $workspace->isArchived()) {
                $processed[] = $this->workspaceManager->unarchive($workspace);
            }
        }

        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Workspace $workspace) {
            return $this->serializer->serialize($workspace);
        }, $processed));
    }

    /**
     * Submit access code.
     *
     * @Route("/unlock/{id}", name="apiv2_workspace_unlock", methods={"POST"})
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     */
    public function unlockAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->restrictionsManager->unlock($workspace, json_decode($request->getContent(), true)['code']);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{id}/users", name="list_users", methods={"GET"})
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     */
    public function listUsersAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $workspace, [], true);

        $workspaceRoles = $this->roleManager->getWorkspaceRoles($workspace);
        $hiddenFilters = [
            'roles' => array_map(function (Role $role) {
                return $role->getUuid();
            }, $workspaceRoles),
        ];

        if (!$this->checkPermission('ROLE_ADMIN')) {
            $hiddenFilters['organizations'] = [];

            $currentUser = $this->tokenStorage->getToken()->getUser();
            if ($currentUser instanceof User) {
                // only list users for the current user organizations
                $hiddenFilters['organizations'] = array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $currentUser->getOrganizations());
            }
        }

        return new JsonResponse(
            $this->crud->list(User::class, array_merge($request->query->all(), [
                'hiddenFilters' => $hiddenFilters,
            ]))
        );
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()->getUser();
            if ($user instanceof User) {
                return [
                    'organizations' => array_map(function (Organization $organization) {
                        return $organization->getUuid();
                    }, $user->getOrganizations()),
                ];
            }
        }

        return [];
    }
}
