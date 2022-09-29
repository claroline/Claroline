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
use Claroline\CoreBundle\Controller\APINew\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasRolesTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\WorkspaceEvents;
use Claroline\CoreBundle\Event\Workspace\CloseWorkspaceEvent;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\LogConnectManager;
use Claroline\CoreBundle\Manager\ResourceManager;
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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/workspace")
 */
class WorkspaceController extends AbstractCrudController
{
    use HasGroupsTrait;
    use HasOrganizationsTrait;
    use HasRolesTrait;
    use HasUsersTrait;
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var RoleManager */
    private $roleManager;
    /** @var ResourceManager */
    private $resourceManager;
    /** @var TranslatorInterface */
    private $translator;
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var LogConnectManager */
    private $logConnectManager;
    /** @var TempFileManager */
    private $tempManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $dispatcher,
        MessageBusInterface $messageBus,
        RoleManager $roleManager,
        ResourceManager $resourceManager,
        TranslatorInterface $translator,
        WorkspaceManager $workspaceManager,
        LogConnectManager $logConnectManager,
        TempFileManager $tempManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->dispatcher = $dispatcher;
        $this->messageBus = $messageBus;
        $this->roleManager = $roleManager;
        $this->resourceManager = $resourceManager;
        $this->translator = $translator;
        $this->workspaceManager = $workspaceManager;
        $this->logConnectManager = $logConnectManager;
        $this->tempManager = $tempManager;
    }

    public function getName()
    {
        return 'workspace';
    }

    public function getClass()
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
     * @Route("/list/registerable", name="apiv2_workspace_list_registerable", methods={"GET"})
     */
    public function listRegisterableAction(Request $request): JsonResponse
    {
        return new JsonResponse($this->finder->search(
            Workspace::class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'displayable' => true,
                'model' => false,
                'selfRegistration' => true,
                'sameOrganization' => true,
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
     * @Route("/list/registered", name="apiv2_workspace_list_registered", methods={"GET"})
     */
    public function listRegisteredAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->finder->search(
            Workspace::class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'model' => false,
                'user' => $this->tokenStorage->getToken()->getUser()->getId(),
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
     * @Route("/list/administrated", name="apiv2_workspace_list_managed", methods={"GET"})
     */
    public function listManagedAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->finder->search(
            Workspace:: class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'administrated' => true,
                'model' => false,
            ]]),
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
     * @Route("/list/model", name="apiv2_workspace_list_model", methods={"GET"})
     */
    public function listModelAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->finder->search(
            Workspace:: class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'model' => true,
            ]]),
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
     * @Route("/list/archived", name="apiv2_workspace_list_archive", methods={"GET"})
     */
    public function listArchivedAction(Request $request): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return new JsonResponse($this->finder->search(
            Workspace:: class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'administrated' => true,
                'archived' => true,
            ]]),
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
     * @ApiDoc(
     *     description="Export the workspace as a zip archive.",
     *     parameters={
     *         {"name": "id", "type": {"string", "integer"},  "description": "The workspace id or uuid"}
     *     }
     * )
     * @Route("/{id}/export", name="apiv2_workspace_export", methods={"GET"})
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
     *     description="Remove workspaces.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of workspace uuids."}
     *     }
     * )
     */
    public function deleteBulkAction(Request $request, $class): JsonResponse
    {
        /** @var Workspace[] $workspaces */
        $workspaces = parent::decodeIdsString($request, Workspace::class);
        $errors = [];

        foreach ($workspaces as $workspace) {
            $notDeletableResources = $this->resourceManager->getNotDeletableResourcesByWorkspace($workspace);

            if (count($notDeletableResources)) {
                $errors[$workspace->getUuid()] = $this->translator->trans(
                    'workspace_not_deletable_resources_error_message',
                    ['%workspaceName%' => $workspace->getName()],
                    'platform'
                );
            }
        }

        if (empty($errors)) {
            return parent::deleteBulkAction($request, Workspace::class);
        }

        $validIds = [];
        $ids = $request->query->get('ids');

        foreach ($ids as $id) {
            if (!isset($errors[$id])) {
                $validIds[] = $id;
            }
        }
        if (count($validIds) > 0) {
            $request->query->set('ids', $validIds);
            parent::deleteBulkAction($request, Workspace::class);
        }

        return new JsonResponse(['errors' => $errors], 422);
    }

    /**
     * @ApiDoc(
     *     description="Archive workspaces.",
     *     queryString={
     *         {"name": "ids", "type": "array", "description": "the list of workspace uuids."}
     *     }
     * )
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
     * @ApiDoc(
     *     description="The manager list of a workspace.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\User&!role",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *         {"name": "id", "type": {"string", "integer"},  "description": "The workspace id or uuid"}
     *     }
     * )
     * @Route("/{id}/managers", name="apiv2_workspace_list_managers", methods={"GET"})
     * @EXT\ParamConverter("workspace", options={"mapping": {"id": "uuid"}})
     */
    public function listManagersAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $workspace, [], true);

        $role = $this->roleManager->getManagerRole($workspace);

        return new JsonResponse($this->finder->search(
            User::class,
            array_merge($request->query->all(), ['hiddenFilters' => ['role' => $role->getUuid()]])
        ));
    }

    /**
     * @ApiDoc(
     *     description="Get the list of common role translation keys between 2 workspaces.",
     *     queryString={
     *         {"name": "workspaces", "type": "array", "description": "The list of workspace uuids."},
     *     }
     * )
     * @Route("/roles/common", name="apiv2_workspace_roles_common", methods={"GET"})
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

    /**
     * @ApiDoc(
     *     description="Dispatches all actions that has to be done when closing a workspace.",
     *     parameters={
     *         {"name": "id", "type": {"string"}, "description": "The workspace uuid"}
     *     }
     * )
     * @Route("/{slug}/close", name="apiv2_workspace_close", methods={"PUT"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"slug": "slug"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function closeAction(Workspace $workspace, User $user = null): JsonResponse
    {
        $this->dispatcher->dispatch(
            WorkspaceEvents::CLOSE,
            CloseWorkspaceEvent::class,
            [$workspace]
        );

        if ($user) {
            // TODO : listen to the close event
            $this->logConnectManager->computeWorkspaceDuration($user, $workspace);
        }

        return new JsonResponse(null, 204);
    }
}
