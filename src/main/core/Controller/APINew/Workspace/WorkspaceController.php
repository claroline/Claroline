<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Workspace;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Messenger\Message\CopyWorkspace;
use Claroline\CoreBundle\Messenger\Message\ImportWorkspace;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/workspace")
 */
class WorkspaceController extends AbstractCrudController
{
    use HasGroupsTrait; // to remove : only the list endpoint is used
    use HasRolesTrait;
    use PermissionCheckerTrait;

    private TokenStorageInterface $tokenStorage;
    private StrictDispatcher $dispatcher;
    private MessageBusInterface $messageBus;
    private RoleManager $roleManager;
    private WorkspaceManager $workspaceManager;
    private LogConnectManager $logConnectManager;
    private TempFileManager $tempManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $dispatcher,
        MessageBusInterface $messageBus,
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager,
        LogConnectManager $logConnectManager,
        TempFileManager $tempManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->dispatcher = $dispatcher;
        $this->messageBus = $messageBus;
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->logConnectManager = $logConnectManager;
        $this->tempManager = $tempManager;
    }

    public function getName(): string
    {
        return 'workspace';
    }

    public function getClass(): string
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
     * @Route("/list/registerable", name="apiv2_workspace_list_registerable", methods={"GET"})
     */
    public function listRegisterableAction(Request $request): JsonResponse
    {
        return new JsonResponse($this->finder->search(
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
     *     description="The list of registered workspaces for the current security token.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Workspace\Workspace&!user",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     *
     * @Route("/list/registered", name="apiv2_workspace_list_registered", methods={"GET"})
     */
    public function listRegisteredAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->finder->search(
            Workspace::class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'model' => false,
                'roles' => $this->tokenStorage->getToken()->getRoleNames(),
            ]]),
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
     * @Route("/list/administrated", name="apiv2_workspace_list_managed", methods={"GET"})
     */
    public function listManagedAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->finder->search(
            Workspace::class,
            array_merge($request->query->all(), ['hiddenFilters' => array_merge($this->getDefaultHiddenFilters(), [
                'administrated' => true,
                'model' => false,
            ])]),
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
     * @Route("/list/model", name="apiv2_workspace_list_model", methods={"GET"})
     */
    public function listModelAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->finder->search(
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
     * @Route("/list/archived", name="apiv2_workspace_list_archive", methods={"GET"})
     */
    public function listArchivedAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->finder->search(
            Workspace::class,
            array_merge($request->query->all(), ['hiddenFilters' => array_merge($this->getDefaultHiddenFilters(), [
                'administrated' => true,
                'archived' => true,
            ])]),
            $this->getOptions()['list']
        ));
    }

    /**
     * @ApiDoc(
     *     description="Copy an array of object of class $class.",
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The object id or uuid."}
     *     }
     * )
     */
    public function copyBulkAction(Request $request, $class): JsonResponse
    {
        $options = $this->getOptions()['copyBulk'];
        if (1 === (int) $request->query->get('model') || 'true' === $request->query->get('model')) {
            $options[] = Options::AS_MODEL;
        }

        $toCopy = $this->decodeIdsString($request, $class);

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
     * @Route("/import", name="apiv2_workspace_import", methods={"POST"})
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
     * @Route("/{id}/export", name="apiv2_workspace_export", methods={"GET"})
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
     * @Route("/archive", name="apiv2_workspace_archive", methods={"PUT"})
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
     * @Route("/unarchive", name="apiv2_workspace_unarchive", methods={"PUT"})
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
     * @Route("/{id}/users", name="apiv2_workspace_list_users", methods={"GET"})
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

    /**
     * @ApiDoc(
     *     description="Get the list of common role translation keys between 2 workspaces.",
     *     queryString={
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     }
     * )
     *
     * @Route("/roles/common", name="apiv2_workspace_roles_common", methods={"GET"})
     *
     * @deprecated
     */
    public function getCommonRolesAction(Request $request): JsonResponse
    {
        /** @var Workspace[] $workspaces */
        $workspaces = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Workspace\Workspace', 'workspaces');

        $roles = [];
        if (1 === count($workspaces)) {
            $roles = $workspaces[0]->getRoles()->toArray();
        } else {
            $all = [];
            foreach ($workspaces as $workspace) {
                foreach ($workspace->getRoles() as $role) {
                    if (!isset($all[$role->getTranslationKey()])) {
                        $all[$role->getTranslationKey()] = [
                            'count' => 1,
                            'instance' => $role,
                        ];
                    } else {
                        ++$all[$role->getTranslationKey()]['count'];
                    }
                }
            }

            // only grab roles used by multiple ws
            foreach ($all as $role) {
                if (1 < $role['count']) {
                    $roles[] = $role['instance'];
                }
            }
        }

        return new JsonResponse(array_map(function (Role $role) {
            return $this->serializer->serialize($role);
        }, $roles));
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
