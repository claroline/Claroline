<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TransferBundle\Controller;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\ToolPermissions;
use Claroline\TransferBundle\Entity\ExportFile;
use Claroline\TransferBundle\Entity\TransferFileInterface;
use Claroline\TransferBundle\Manager\ExportManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/transfer_export', name: 'apiv2_transfer_export_')]
class ExportController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ExportManager $exportManager,
        private readonly string $filesDir
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'transfer_export';
    }

    public static function getClass(): string
    {
        return ExportFile::class;
    }

    #[Route(path: '/workspace/{workspaceId}', name: 'workspace_list', methods: ['GET'])]
    public function listByWorkspaceAction(
        #[MapEntity(mapping: ['workspaceId' => 'uuid'])]
        Workspace $workspace,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission(ToolPermissions::getPermission('export', 'OPEN'), $workspace, [], true);

        $finderQuery->addFilter('workspace', $workspace->getUuid());

        $exports = $this->crud->search(ExportFile::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $exports->toResponse();
    }

    #[Route(path: '/{id}/execute', name: 'execute', methods: ['POST'])]
    public function executeAction(#[MapEntity(mapping: ['id' => 'uuid'])] ExportFile $exportFile): JsonResponse
    {
        $this->checkPermission('REFRESH', $exportFile, [], true);

        $this->exportManager->requestExport($exportFile);

        return new JsonResponse(
            $this->serializer->serialize($exportFile),
            TransferFileInterface::IN_PROGRESS === $exportFile->getStatus() ? 202 : 200
        );
    }

    #[Route(path: '/{id}/download', name: 'download', methods: ['GET'])]
    public function downloadAction(#[MapEntity(mapping: ['id' => 'uuid'])] ExportFile $exportFile): BinaryFileResponse
    {
        $this->checkPermission('OPEN', $exportFile, [], true);

        $filename = TextNormalizer::toKey(
            $exportFile->getAction().'-'.DateNormalizer::normalize($exportFile->getExecutionDate())
        ).'.'.$exportFile->getFormat();

        return new BinaryFileResponse($this->filesDir.DIRECTORY_SEPARATOR.'transfer'.DIRECTORY_SEPARATOR.$exportFile->getUuid(), 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }
}
