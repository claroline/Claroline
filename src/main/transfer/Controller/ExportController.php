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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\ToolPermissions;
use Claroline\TransferBundle\Entity\ExportFile;
use Claroline\TransferBundle\Manager\ExportManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
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

    /**
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspaceId": "uuid"}})
     */
    #[Route(path: '/workspace/{workspaceId}', name: 'workspace_list', methods: ['GET'])]
    public function listByWorkspaceAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkPermission(ToolPermissions::getPermission('export', 'OPEN'), $workspace, [], true);

        return new JsonResponse(
            $this->crud->list(self::getClass(), array_merge($request->query->all(), ['hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
            ]]), $this->getOptions()['list'] ?? [])
        );
    }

    /**
     * @EXT\ParamConverter("exportFile", options={"mapping": {"id": "uuid"}})
     */
    #[Route(path: '/{id}/execute', name: 'execute', methods: ['POST'])]
    public function executeAction(ExportFile $exportFile): JsonResponse
    {
        $this->checkPermission('REFRESH', $exportFile, [], true);

        $this->exportManager->requestExport($exportFile);

        return new JsonResponse(
            $this->serializer->serialize($exportFile),
            ExportFile::IN_PROGRESS === $exportFile->getStatus() ? 202 : 200
        );
    }

    /**
     * @EXT\ParamConverter("exportFile", options={"mapping": {"id": "uuid"}})
     */
    #[Route(path: '/{id}/download', name: 'download', methods: ['GET'])]
    public function downloadAction(ExportFile $exportFile): BinaryFileResponse
    {
        $this->checkPermission('OPEN', $exportFile, [], true);

        $filename = TextNormalizer::toKey(
            $exportFile->getAction().'-'.DateNormalizer::normalize($exportFile->getExecutionDate())
        ).'.'.$exportFile->getFormat();

        return new BinaryFileResponse($this->filesDir.DIRECTORY_SEPARATOR.'transfer'.DIRECTORY_SEPARATOR.$exportFile->getUuid(), 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
