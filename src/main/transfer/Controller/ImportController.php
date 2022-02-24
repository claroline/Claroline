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
use Claroline\TransferBundle\Entity\ImportFile;
use Claroline\TransferBundle\Manager\TransferManager;
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
 * @Route("/transfer_import")
 */
class ImportController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ImportProvider */
    private $provider;
    /** @var TransferManager */
    private $transferManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ImportProvider $provider,
        TransferManager $transferManager
    ) {
        $this->authorization = $authorization;
        $this->provider = $provider;
        $this->transferManager = $transferManager;
    }

    public function getName()
    {
        return 'transfer_import';
    }

    public function getClass()
    {
        return ImportFile::class;
    }

    /**
     * @Route("/workspace/{workspaceId}", name="apiv2_workspace_transfer_import_list", methods={"GET"})
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
     * @Route("/{id}/execute", name="apiv2_transfer_import_execute", methods={"POST"})
     * @EXT\ParamConverter("importFile", options={"mapping": {"id": "uuid"}})
     */
    public function executeAction(ImportFile $importFile): JsonResponse
    {
        $this->checkPermission('EDIT', $importFile, [], true);

        $this->transferManager->requestImport($importFile);

        return new JsonResponse(
            $this->serializer->serialize($importFile)
        );
    }

    /**
     * @Route("/{id}/log", name="apiv2_transfer_import_log", methods={"get"})
     * @EXT\ParamConverter("importFile", options={"mapping": {"id": "uuid"}})
     */
    public function logAction(ImportFile $importFile): Response
    {
        $this->checkPermission('OPEN', $importFile, [], true);

        $logs = $this->transferManager->getLog($importFile);
        if (empty($logs)) {
            throw new NotFoundHttpException('Log for import cannot be found');
        }

        return new Response($logs);
    }

    /**
     * @Route("/action/{format}", name="apiv2_transfer_import_actions", methods={"GET"})
     */
    public function getAvailableActionsAction(string $format): JsonResponse
    {
        return new JsonResponse(
            $this->provider->getAvailableActions($format)
        );
    }

    /**
     * @Route("/sample/{format}/{entity}/{name}/{sample}", name="apiv2_transfer_import_sample", methods={"GET"})
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
