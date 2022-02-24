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
use Claroline\TransferBundle\Entity\ExportFile;
use Claroline\TransferBundle\Manager\TransferManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/transfer_export")
 */
class ExportController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TransferManager */
    private $transferManager;
    /** @var string */
    private $filesDir;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TransferManager $transferManager,
        string $filesDir
    ) {
        $this->authorization = $authorization;
        $this->transferManager = $transferManager;
        $this->filesDir = $filesDir;
    }

    public function getName()
    {
        return 'transfer_export';
    }

    public function getClass()
    {
        return ExportFile::class;
    }

    /**
     * @Route("/workspace/{workspaceId}", name="apiv2_workspace_transfer_export_list", methods={"GET"})
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspaceId": "uuid"}})
     */
    public function listByWorkspaceAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkPermission(['transfer', 'open'], $workspace, [], true);

        return new JsonResponse(
            $this->finder->search(self::getClass(), array_merge($request->query->all(), ['hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
            ]]), $this->getOptions()['list'] ?? [])
        );
    }

    /**
     * @Route("/{id}/execute", name="apiv2_transfer_export_execute", methods={"POST"})
     * @EXT\ParamConverter("exportFile", options={"mapping": {"id": "uuid"}})
     */
    public function executeAction(ExportFile $exportFile): JsonResponse
    {
        $this->checkPermission('EDIT', $exportFile, [], true);

        $this->transferManager->requestExport($exportFile);

        return new JsonResponse(
            $this->serializer->serialize($exportFile)
        );
    }

    /**
     * @Route("/{id}/download", name="apiv2_transfer_export_download", methods={"GET"})
     * @EXT\ParamConverter("exportFile", options={"mapping": {"id": "uuid"}})
     */
    public function downloadAction(ExportFile $exportFile): BinaryFileResponse
    {
        $this->checkPermission('OPEN', $exportFile, [], true);

        $filename = TextNormalizer::toKey(
            $exportFile->getAction().'-'.DateNormalizer::normalize($exportFile->getExecutionDate())
        ).'.'.$exportFile->getFormat();

        return new BinaryFileResponse($this->filesDir.DIRECTORY_SEPARATOR.'transfer'.DIRECTORY_SEPARATOR.$exportFile->getUuid(), 200, [
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }
}
