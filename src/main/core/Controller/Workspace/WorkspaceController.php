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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AuthenticationBundle\Messenger\Stamp\AuthenticationStamp;
use Claroline\CoreBundle\Controller\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\Model\HasOrganizationsTrait;
use Claroline\CoreBundle\Controller\Model\HasRolesTrait;
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
    use HasGroupsTrait; // to remove: only the list endpoint is used
    use HasRolesTrait; // to remove: only the list endpoint is used
    use HasOrganizationsTrait;
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

    #[Route(path: '/list/registered', name: 'list_registered', methods: ['GET'])]
    public function listRegisteredAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return $this->crud
            ->search(Workspace::class, $finderQuery->addFilters([
                'roles' => $this->tokenStorage->getToken()->getRoleNames(),
            ]), [SerializerInterface::SERIALIZE_LIST])
            ->toResponse();
    }

    #[Route(path: '/list/model', name: 'list_model', methods: ['GET'])]
    public function listModelAction(
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return $this->crud
            ->search(Workspace::class, $finderQuery->addFilters([
                'model' => true,
            ]), [SerializerInterface::SERIALIZE_LIST])
            ->toResponse();
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
    #[Route(path: '/copy', name: 'copy', methods: ['POST'])]
    public function copyAction(Request $request): JsonResponse
    {
        $options = [Crud::NO_PERMISSIONS];
        if (1 === (int) $request->query->get('model') || 'true' === $request->query->get('model')) {
            $options[] = Options::AS_MODEL;
        }

        $workspaceIds = $this->decodeRequest($request);

        foreach ($workspaceIds as $workspaceId) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $workspaceId]);
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
    #[Route(path: '/unlock/{id}', name: 'unlock', methods: ['POST'])]
    public function unlockAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
        Request $request
    ): JsonResponse {
        $this->restrictionsManager->unlock($workspace, json_decode($request->getContent(), true)['code']);

        return new JsonResponse(null, 204);
    }
}
