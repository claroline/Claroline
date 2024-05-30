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
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\EvaluationBundle\Manager\CertificateManager;
use Claroline\EvaluationBundle\Manager\PdfManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
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
        private readonly PdfManager $pdfManager,
        private readonly CertificateManager $certificateManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * @Route("/{workspace}/{user}/{type}", name="apiv2_workspace_download_certificate", methods={"GET"})
     *
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function downloadCertificateAction(Workspace $workspace, User $user, string $type): StreamedResponse
    {
        $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
            'workspace' => $workspace,
            'user' => $user,
        ]);

        if (empty($workspaceEvaluation)) {
            throw new NotFoundHttpException('Workspace evaluation not found.');
        }

        $this->checkPermission('OPEN', $workspaceEvaluation, [], true);

        switch ($type) {
            case 'participation':
                $certificate = $this->pdfManager->getWorkspaceParticipationCertificate($workspaceEvaluation);
                $fileNameSuffix = '-participation.pdf';
                break;
            case 'success':
                $certificate = $this->pdfManager->getWorkspaceSuccessCertificate($workspaceEvaluation);
                $fileNameSuffix = '-success.pdf';
                break;
            default:
                throw new NotFoundHttpException('Invalid certificate type.');
        }

        if (empty($certificate)) {
            throw new NotFoundHttpException('No certificate is available yet.');
        }

        return new StreamedResponse(function () use ($certificate) {
            echo $certificate;
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($workspace->getName()).'-'.TextNormalizer::toKey($user->getFullName()).$fileNameSuffix,
        ]);
    }

    /**
     * @Route("/certificates/participation", name="apiv2_workspace_download_participation_certificates", methods={"POST"})
     *
     * @throws InvalidDataException
     */
    public function downloadParticipationCertificatesAction(Request $request): BinaryFileResponse
    {
        $workspaceEvaluationsIds = $this->decodeRequest($request);

        $evaluations = [];
        foreach ($workspaceEvaluationsIds as $workspaceEvaluationId) {
            $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
                'uuid' => $workspaceEvaluationId,
            ]);

            if ($this->checkPermission('OPEN', $workspaceEvaluation)) {
                $evaluations[] = $workspaceEvaluation;
            }
        }

        // either we get the path to an archive or the path to a PDF if ony one certificate
        $certificateFile = $this->pdfManager->getWorkspaceParticipationCertificates($evaluations);
        if (empty($certificateFile)) {
            throw new NotFoundHttpException('No participation certificates found for these ids.');
        }

        return new BinaryFileResponse($certificateFile[1], 200, [
            'Content-Disposition' => "attachment; filename={$certificateFile[0]}",
        ]);
    }

    /**
     * @Route("/certificates/success", name="apiv2_workspace_download_success_certificates", methods={"POST"})
     *
     * @throws InvalidDataException
     */
    public function downloadSuccessCertificatesAction(Request $request): BinaryFileResponse
    {
        $workspaceEvaluationsIds = $this->decodeRequest($request);

        $evaluations = [];
        foreach ($workspaceEvaluationsIds as $workspaceEvaluationId) {
            $workspaceEvaluation = $this->om->getRepository(Evaluation::class)->findOneBy([
                'uuid' => $workspaceEvaluationId,
            ]);

            if ($this->checkPermission('OPEN', $workspaceEvaluation)) {
                $evaluations[] = $workspaceEvaluation;
            }
        }

        // either we get the path to an archive or the path to a PDF if ony one certificate
        $certificateFile = $this->pdfManager->getWorkspaceSuccessCertificates($evaluations);
        if (empty($certificateFile)) {
            throw new NotFoundHttpException('No participation certificates found for these ids.');
        }

        return new BinaryFileResponse($certificateFile[1], 200, [
            'Content-Disposition' => "attachment; filename={$certificateFile[0]}",
        ]);
    }
}
