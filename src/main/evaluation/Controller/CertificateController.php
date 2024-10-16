<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Controller;

use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Manager\CertificateManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/certificate")
 */
class CertificateController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly CertificateManager $certificateManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * @Route("/", name="apiv2_workspace_download_certificate", methods={"POST"})
     */
    public function downloadCertificateAction(Request $request): Response
    {
        $workspaceEvaluationIds = $this->decodeRequest($request);

        if (!empty($workspaceEvaluationIds)) {
            $workspaceEvaluations = $this->om->getRepository(Evaluation::class)->findBy(['uuid' => $workspaceEvaluationIds]);
            if (!empty($workspaceEvaluations)) {
                $workspace = $workspaceEvaluations[0]->getWorkspace();

                return $this->downloadCertificates($workspace, $workspaceEvaluations);
            }
        }

        throw new NotFoundHttpException('No workspace evaluation found.');
    }

    /**
     * @Route("/{workspace}/all", name="apiv2_workspace_download_all_certificates", methods={"GET"})
     *
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function downloadAllCertificatesAction(Workspace $workspace): Response
    {
        $workspaceEvaluations = $this->om->getRepository(Evaluation::class)->findBy([
            'workspace' => $workspace,
        ]);

        return $this->downloadCertificates($workspace, $workspaceEvaluations);
    }

    /**
     * @Route("/{workspace}/user/{user}", name="apiv2_workspace_download_user_certificate", methods={"GET"})
     *
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function downloadUserCertificateAction(Workspace $workspace, User $user): Response
    {
        $workspaceEvaluations = $this->om->getRepository(Evaluation::class)->findBy([
            'workspace' => $workspace,
            'user' => $user,
        ]);

        return $this->downloadCertificates($workspace, $workspaceEvaluations);
    }

    /**
     * @Route("/{evaluation}/generate", name="apiv2_workspace_generate_user_certificate", methods={"GET"})
     *
     * @EXT\ParamConverter("evaluation", class="Claroline\CoreBundle\Entity\Workspace\Evaluation", options={"mapping": {"evaluation": "uuid"}})
     */
    public function regenerateUserCertificateAction(Evaluation $evaluation): BinaryFileResponse
    {
        $this->checkPermission('OPEN', $evaluation);
        $workspace = $evaluation->getWorkspace();

        $pdfFilepath = $this->certificateManager->getCertificate($evaluation, true);

        return new BinaryFileResponse($pdfFilepath, 200, [
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($workspace->getName()).'.pdf',
        ]);
    }

    /**
     * @Route("/regenerate", name="apiv2_workspace_regenerate_certificate", methods={"POST"})
     */
    public function regenerateCertificateAction(Request $request): Response
    {
        $workspaceEvaluationIds = $this->decodeRequest($request);

        if (!empty($workspaceEvaluationIds)) {
            $workspaceEvaluations = $this->om->getRepository(Evaluation::class)->findBy(['uuid' => $workspaceEvaluationIds]);
            if (!empty($workspaceEvaluations)) {
                foreach ($workspaceEvaluations as $evaluation) {
                    $this->checkPermission('OPEN', $evaluation);
                    $this->certificateManager->getCertificate($evaluation, true);
                }

                return $this->downloadCertificateAction($request);
            }
        }

        throw new NotFoundHttpException('No workspace evaluation found.');
    }

    private function downloadCertificates(Workspace $workspace, array $workspaceEvaluations): Response
    {
        if (empty($workspaceEvaluations)) {
            throw new NotFoundHttpException('No workspace evaluation found.');
        }

        $certificateFiles = [];
        foreach ($workspaceEvaluations as $evaluation) {
            if ($this->checkPermission('OPEN', $evaluation)) {
                $certificateFiles[] = $this->certificateManager->getCertificate($evaluation);
            }
        }

        if (empty($certificateFiles)) {
            throw new NotFoundHttpException('No certificate is available yet.');
        }

        if (count($certificateFiles) > 1) {
            $archive = $this->certificateManager->createArchive($certificateFiles);

            return new BinaryFileResponse($archive, 200, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($workspace->getName()).'.zip',
            ]);
        } else {
            $certificate = file_get_contents($certificateFiles[0]);

            return new StreamedResponse(function () use ($certificate) {
                echo $certificate;
            }, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($workspace->getName()).'.pdf',
            ]);
        }
    }
}
