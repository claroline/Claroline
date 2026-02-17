<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Controller\Certificate;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\Certificate\WorkspaceCertificate;
use Claroline\EvaluationBundle\Entity\UserEvaluation\WorkspaceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Manager\WorkspaceCertificateManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/workspace_certificate')]
class WorkspaceCertificateController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly WorkspaceCertificateManager $certificateManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Download the certificates for a list of workspace evaluations.
     */
    #[Route(path: '/', name: 'apiv2_workspace_download_certificate', methods: ['POST'])]
    public function downloadCertificateAction(Request $request): BinaryFileResponse
    {
        $workspaceEvaluationIds = $this->decodeRequest($request);

        if (!empty($workspaceEvaluationIds)) {
            $workspaceEvaluations = $this->om->getRepository(WorkspaceEvaluation::class)->findBy(['uuid' => $workspaceEvaluationIds]);
            if (!empty($workspaceEvaluations)) {
                $workspace = $workspaceEvaluations[0]->getWorkspace();

                return $this->downloadCertificates($workspace, $workspaceEvaluations);
            }
        }

        throw new NotFoundHttpException('No workspace evaluation found.');
    }

    #[Route(path: '/{certificateId}', name: 'apiv2_workspace_certificate_download', methods: ['GET'])]
    public function downloadAction(
        #[MapEntity(mapping: ['certificateId' => 'uuid'])]
        WorkspaceCertificate $certificate,
    ): BinaryFileResponse {
        $this->checkPermission('OPEN', $certificate, [], true);

        $workspace = $certificate->getEvaluation()->getWorkspace();

        return new BinaryFileResponse($this->certificateManager->getCertificateFile($certificate), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($workspace->getName()).'.pdf',
        ]);
    }

    #[Route(path: '/', name: 'apiv2_workspace_certificate_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request): JsonResponse
    {
        // no need to secure endpoint CRUD will do it for us

        $certificateIds = $this->decodeRequest($request);
        $certificates = $this->om->getRepository(WorkspaceCertificate::class)->findBy(['uuid' => $certificateIds]);
        foreach ($certificates as $certificate) {
            $this->crud->delete($certificate);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Download all the certificates obtained for a workspace.
     */
    #[Route(path: '/{workspace}/all', name: 'apiv2_workspace_download_all_certificates', methods: ['GET'])]
    public function downloadAllCertificatesAction(
        #[MapEntity(mapping: ['workspace' => 'uuid'])]
        Workspace $workspace
    ): BinaryFileResponse {
        $workspaceEvaluations = $this->om->getRepository(WorkspaceEvaluation::class)->findBy([
            'workspace' => $workspace,
        ]);

        return $this->downloadCertificates($workspace, $workspaceEvaluations);
    }

    /**
     * Regenerate the certificates for a list of workspace evaluations.
     */
    #[Route(path: '/regenerate', name: 'apiv2_workspace_regenerate_certificate', methods: ['POST'])]
    public function regenerateCertificateAction(Request $request): BinaryFileResponse
    {
        $workspaceEvaluationIds = $this->decodeRequest($request);

        if (!empty($workspaceEvaluationIds)) {
            $workspaceEvaluations = $this->om->getRepository(WorkspaceEvaluation::class)->findBy(['uuid' => $workspaceEvaluationIds]);
            if (!empty($workspaceEvaluations)) {
                return $this->downloadCertificates($workspaceEvaluations[0]->getWorkspace(), $workspaceEvaluations, true);
            }
        }

        throw new NotFoundHttpException('No workspace evaluation found.');
    }

    /**
     * Regenerate all the certificates of a workspace.
     */
    #[Route(path: '/{workspace}/regenerate', name: 'apiv2_workspace_regenerate_all_certificates', methods: ['PUT'])]
    public function regenerateAllAction(
        #[MapEntity(mapping: ['sequence' => 'uuid'])]
        Workspace $workspace
    ): JsonResponse {
        $this->checkPermission('EDIT', $workspace, [], true);

        $workspaceEvaluations = $this->om->getRepository(WorkspaceEvaluation::class)->findBy(['sequence' => $workspace]);
        foreach ($workspaceEvaluations as $workspaceEvaluation) {
            if (in_array($workspaceEvaluation->getStatus(), [EvaluationStatus::COMPLETED, EvaluationStatus::PASSED])) {
                $this->certificateManager->getCertificate($workspaceEvaluation, true);
            }
        }

        return new JsonResponse(null, 204);
    }

    private function downloadCertificates(Workspace $workspace, array $workspaceEvaluations, bool $regenerate = false): BinaryFileResponse
    {
        if (empty($workspaceEvaluations)) {
            throw new NotFoundHttpException('No workspace evaluation found.');
        }
        $certificateFiles = [];

        foreach ($workspaceEvaluations as $evaluation) {
            if (
                in_array($evaluation->getStatus(), [EvaluationStatus::COMPLETED, EvaluationStatus::PASSED], true)
                && $this->checkPermission('OPEN', $evaluation)
            ) {
                $path = $this->certificateManager->getCertificate($evaluation, $regenerate);

                $evaluationUserName =
                    $evaluation->getUser()->getLastName().'-'.$evaluation->getUser()->getFirstName();

                $filenameInZip = TextNormalizer::toKey(
                    $evaluationUserName.'-'.$workspace->getCode()
                ).'.pdf';

                // ensure single name file for 2 same username
                $base = $filenameInZip;
                $i = 2;
                while (in_array($filenameInZip, $certificateFiles, true)) {
                    $filenameInZip = preg_replace('/\.pdf$/', '', $base)."-{$i}.pdf";
                    ++$i;
                }

                $certificateFiles[$path] = $filenameInZip;
            }
        }

        if (empty($certificateFiles)) {
            throw new NotFoundHttpException('No certificate is available yet.');
        }

        if (count($certificateFiles) > 1) {
            $archive = $this->certificateManager->createArchive($certificateFiles);

            return new BinaryFileResponse($archive, 200, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($workspace->getCode()).'.zip',
            ]);
        }

        $singlePath = array_key_first($certificateFiles);
        $singleName = $certificateFiles[$singlePath];

        return new BinaryFileResponse($singlePath, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.$singleName,
        ]);
    }
}
