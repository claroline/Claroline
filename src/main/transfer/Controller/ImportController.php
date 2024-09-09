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
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\ToolPermissions;
use Claroline\TransferBundle\Entity\ImportFile;
use Claroline\TransferBundle\Manager\ImportManager;
use Claroline\TransferBundle\Transfer\ImportProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/transfer_import", name="apiv2_transfer_import_")
 */
class ImportController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ImportProvider $provider,
        private readonly ImportManager $importManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'transfer_import';
    }

    public static function getClass(): string
    {
        return ImportFile::class;
    }

    /**
     * @Route("/workspace/{workspaceId}", name="workspace_list", methods={"GET"})
     *
     * @EXT\ParamConverter("workspace", options={"mapping": {"workspaceId": "uuid"}})
     */
    public function listByWorkspaceAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkPermission(ToolPermissions::getPermission('import', 'OPEN'), $workspace, [], true);

        return new JsonResponse(
            $this->crud->list(self::getClass(), array_merge($request->query->all(), ['hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
            ]]), $this->getOptions()['list'] ?? [])
        );
    }

    /**
     * @Route("/{id}/execute", name="execute", methods={"POST"})
     *
     * @EXT\ParamConverter("importFile", options={"mapping": {"id": "uuid"}})
     */
    public function executeAction(ImportFile $importFile): JsonResponse
    {
        $this->checkPermission('EDIT', $importFile, [], true);

        $this->importManager->requestImport($importFile);

        return new JsonResponse(
            $this->serializer->serialize($importFile),
            ImportFile::IN_PROGRESS === $importFile->getStatus() ? 202 : 200
        );
    }

    /**
     * @Route("/{id}/log", name="log", methods={"GET"})
     *
     * @EXT\ParamConverter("importFile", options={"mapping": {"id": "uuid"}})
     */
    public function logAction(ImportFile $importFile): Response
    {
        $this->checkPermission('OPEN', $importFile, [], true);

        $logs = $this->importManager->getLog($importFile);
        if (empty($logs)) {
            throw new NotFoundHttpException('Log for import cannot be found');
        }

        return new Response($logs);
    }

    /**
     * @Route("/sample/{format}/{entity}/{name}/{sample}", name="sample", methods={"GET"})
     */
    public function downloadSampleAction(string $name, string $format, string $entity, string $sample): BinaryFileResponse
    {
        $file = $this->provider->getSamplePath($format, $entity, $name, $sample);
        if (!$file) {
            throw new NotFoundHttpException('Sample file not found.');
        }

        return new BinaryFileResponse($file, 200, [
            'Content-Disposition' => "attachment; filename={$sample}",
        ]);
    }
}
