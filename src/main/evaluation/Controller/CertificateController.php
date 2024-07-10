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
    public function downloadCertificateAction(Request $request): BinaryFileResponse
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
    public function downloadAllCertificatesAction(Workspace $workspace): BinaryFileResponse
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
    public function downloadUserCertificateAction(Workspace $workspace, User $user): BinaryFileResponse
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
        $this->checkPermission('OPEN', $evaluation, [], true);

        return $this->downloadCertificates($evaluation->getWorkspace(), [$evaluation], true);
    }

    /**
     * @Route("/regenerate", name="apiv2_workspace_regenerate_certificate", methods={"POST"})
     */
    public function regenerateCertificateAction(Request $request): BinaryFileResponse
    {
        $workspaceEvaluationIds = $this->decodeRequest($request);

        if (!empty($workspaceEvaluationIds)) {
            $workspaceEvaluations = $this->om->getRepository(Evaluation::class)->findBy(['uuid' => $workspaceEvaluationIds]);
            if (!empty($workspaceEvaluations)) {
                return $this->downloadCertificates($workspaceEvaluations[0]->getWorkspace(), $workspaceEvaluations, true);
            }
        }

        throw new NotFoundHttpException('No workspace evaluation found.');
    }

    private function downloadCertificates(Workspace $workspace, array $workspaceEvaluations, bool $regenerate = false): BinaryFileResponse
    {
        if (empty($workspaceEvaluations)) {
            throw new NotFoundHttpException('No workspace evaluation found.');
        }

        $certificateFiles = [];
        foreach ($workspaceEvaluations as $evaluation) {
            if ($this->checkPermission('OPEN', $evaluation)) {
                $certificateFiles[] = $this->certificateManager->getCertificate($evaluation, $regenerate);
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
        }

        return new BinaryFileResponse($certificateFiles[0], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($workspace->getName()).'.pdf',
        ]);
    }
}
