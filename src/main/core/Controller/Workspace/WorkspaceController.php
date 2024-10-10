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
use Claroline\AppBundle\API\Finder\FinderFactory;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Controller\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\Model\HasRolesTrait;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Finder\WorkspaceType;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceRestrictionsManager;
use Claroline\CoreBundle\Messenger\Message\CopyWorkspace;
use Claroline\CoreBundle\Messenger\Message\CreateWorkspace;
use Claroline\CoreBundle\Messenger\Message\ImportWorkspace;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\TransferBundle\Finder\ImportFileType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/workspace', name: 'apiv2_workspace_')]
class WorkspaceController extends AbstractCrudController
{
    use HasGroupsTrait; // to remove : only the list endpoint is used
    use HasRolesTrait; // to remove : only the list endpoint is used
    use PermissionCheckerTrait;

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        private readonly MessageBusInterface $messageBus,
        private readonly TempFileManager $tempManager,
        private readonly RoleManager $roleManager,
        private readonly WorkspaceManager $workspaceManager,
        private readonly WorkspaceRestrictionsManager $restrictionsManager,
        private readonly FinderFactory $finder
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
     *     description="The list of public workspaces for the current security token.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     }
     * )
     */
    #[Route(path: '/list/public', name: 'list_public', methods: ['GET'])]
    public function listPublicAction(Request $request): JsonResponse
    {
        return new JsonResponse($this->crud->list(
            Workspace::class,
            array_merge($request->query->all(), ['hiddenFilters' => [
                'displayable' => true,
                'model' => false,
                'public' => true,
            ]]),
            static::getOptions()['list']
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
     */
    #[Route(path: '/list/registered', name: 'list_registered', methods: ['GET'])]
    public function listRegisteredAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $workspaces = $this->finder->create(WorkspaceType::class)
            ->submit($finderQuery->addFilters([
                'roles' => $this->tokenStorage->getToken()->getRoleNames(),
            ]))
            ->getResult(function (Workspace $workspace): array {
                return $this->serializer->serialize($workspace, [SerializerInterface::SERIALIZE_LIST]);
            })
        ;

        return new StreamedJsonResponse([
            'totalResults' => $workspaces->count(),
            'data' => $workspaces->getItems(),
        ]);
    }

    #[Route(path: '/test', name: 'test', methods: ['GET'])]
    public function testAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $finder = $this->finder->create(ImportFileType::class)
            ->submit($finderQuery)
            ->getResult(function (object $row): array {
                return $this->serializer->serialize($row, [SerializerInterface::SERIALIZE_MINIMAL]);
            })
        ;

        $queryParams = $finder->getQuery()->getParameters()->toArray();

        return new StreamedJsonResponse([
            'sql' => $finder->getQuery()->getSQL(),
            'parameters' => array_map(function ($parameter) {
                return [
                    'name' => $parameter->getName(),
                    'type' => $parameter->getType(),
                    'value' => $parameter->getValue(),
                ];
            }, $queryParams),
            'data' => $finder->getItems(),
        ]);
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
     */
    #[Route(path: '/list/administrated', name: 'list_managed', methods: ['GET'])]
    public function listManagedAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $workspaces = $this->finder->create(WorkspaceType::class)
            ->submit($finderQuery->addFilters([
                // 'model' => false,
                // 'roles' => $this->tokenStorage->getToken()->getRoleNames(),
                // 'administrated' => true,
            ]))
            ->getResult(function (Workspace $workspace): array {
                return $this->serializer->serialize($workspace, [SerializerInterface::SERIALIZE_LIST]);
            })
        ;

        return new StreamedJsonResponse([
            'totalResults' => $workspaces->count(),
            'data' => $workspaces->getItems(),
        ]);
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
     */
    #[Route(path: '/list/model', name: 'list_model', methods: ['GET'])]
    public function listModelAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $finderQuery->addFilters([
            'model' => true,
            /*'roles' => $this->tokenStorage->getToken()->getRoleNames(),
            'administrated' => true,*/
        ]);

        $models = $this->crud->search(Workspace::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $models->toResponse();
    }

    #[Route(path: '/', name: 'create', methods: ['POST'])]
    public function createAction(Request $request): JsonResponse
    {
        $this->checkPermission('CREATE', new Workspace(), [], true);

        $options = static::getOptions();

        $this->messageBus->dispatch(new CreateWorkspace(
            $this->decodeRequest($request),
            $options['create'] ?? []
        ), [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]);

        return new JsonResponse(
            null,
            204
        );
    }

    /**
     * Copies a list of workspaces.
     */
    #[Route(path: '/copy', name: 'copy', methods: ['PUT'])]
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
                ), [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]);
            }
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Creates a new Workspace from a Claroline archive.
     */
    #[Route(path: '/import', name: 'import', methods: ['POST'])]
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
        ), [new AuthenticationStamp($this->tokenStorage->getToken()?->getUser()->getId())]);

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
     */
    #[Route(path: '/{id}/export', name: 'export', methods: ['GET'])]
    public function exportAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace
    ): BinaryFileResponse {
        $this->checkPermission('OPEN', $workspace, [], true);

        $pathArch = $this->workspaceManager->export($workspace);
        $filename = TextNormalizer::toKey($workspace->getCode()).'.zip';

        $response = new BinaryFileResponse($pathArch);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', "attachment; filename=$filename");

        return $response;
    }

    /**
     * Submit access code.
     */
    #[Route(path: '/unlock/{id}', name: 'apiv2_workspace_unlock', methods: ['POST'])]
    public function unlockAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
        Request $request
    ): JsonResponse {
        $this->restrictionsManager->unlock($workspace, json_decode($request->getContent(), true)['code']);

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/{id}/users', name: 'list_users', methods: ['GET'])]
    public function listUsersAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('OPEN', $workspace, [], true);

        $workspaceRoles = $this->roleManager->getWorkspaceRoles($workspace);
        $finderQuery->addFilter('roles', array_map(function (Role $role) {
            return $role->getName();
        }, $workspaceRoles));

        $users = $this->crud->search(User::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $users->toResponse();
    }

    protected function getDefaultHiddenFilters(): array
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            $user = $this->tokenStorage->getToken()?->getUser();
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
