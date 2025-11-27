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
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\EvaluationBundle\Entity\Certificate\SequenceCertificate;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Manager\SequenceCertificateManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/sequence_certificate')]
class SequenceCertificateController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly Crud $crud,
        private readonly SequenceCertificateManager $certificateManager
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Download the certificates (in PDF) for a list of sequence evaluations.
     */
    #[Route(path: '/', name: 'apiv2_sequence_download_certificate', methods: ['POST'])]
    public function downloadForEvaluationsAction(Request $request): BinaryFileResponse
    {
        $sequenceEvaluationIds = $this->decodeRequest($request);

        if (!empty($sequenceEvaluationIds)) {
            $sequenceEvaluations = $this->om->getRepository(SequenceEvaluation::class)->findBy(['uuid' => $sequenceEvaluationIds]);
            if (!empty($sequenceEvaluations)) {
                $sequence = $sequenceEvaluations[0]->getSequence();

                return $this->downloadCertificates($sequence, $sequenceEvaluations);
            }
        }

        throw new NotFoundHttpException('No sequence evaluation found.');
    }

    #[Route(path: '/{certificateId}', name: 'apiv2_sequence_certificate_download', methods: ['GET'])]
    public function downloadAction(
        #[MapEntity(mapping: ['certificateId' => 'uuid'])]
        SequenceCertificate $certificate,
    ): BinaryFileResponse {
        $this->checkPermission('OPEN', $certificate, [], true);

        $sequence = $certificate->getEvaluation()->getSequence();

        return new BinaryFileResponse($this->certificateManager->getCertificateFile($certificate), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($sequence->getName()).'.pdf',
        ]);
    }

    #[Route(path: '/', name: 'apiv2_sequence_certificate_delete', methods: ['DELETE'])]
    public function deleteAction(Request $request): JsonResponse
    {
        // no need to secure endpoint CRUD will do it for us

        $certificateIds = $this->decodeRequest($request);
        $certificates = $this->om->getRepository(SequenceCertificate::class)->findBy(['uuid' => $certificateIds]);
        foreach ($certificates as $certificate) {
            $this->crud->delete($certificate);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * Download all the certificates obtained for a sequence.
     */
    #[Route(path: '/{sequence}/all', name: 'apiv2_sequence_download_all_certificates', methods: ['GET'])]
    public function downloadAllAction(
        #[MapEntity(mapping: ['sequence' => 'uuid'])]
        Sequence $sequence
    ): BinaryFileResponse {
        $sequenceEvaluations = $this->om->getRepository(SequenceEvaluation::class)->findBy([
            'sequence' => $sequence,
        ]);

        return $this->downloadCertificates($sequence, $sequenceEvaluations);
    }

    /**
     * Regenerate the certificates for a list of sequence evaluations.
     */
    #[Route(path: '/regenerate', name: 'apiv2_sequence_regenerate_certificate', methods: ['POST'])]
    public function regenerateAction(Request $request): BinaryFileResponse
    {
        $sequenceEvaluationIds = $this->decodeRequest($request);

        if (!empty($sequenceEvaluationIds)) {
            $sequenceEvaluations = $this->om->getRepository(SequenceEvaluation::class)->findBy(['uuid' => $sequenceEvaluationIds]);
            if (!empty($sequenceEvaluations)) {
                return $this->downloadCertificates($sequenceEvaluations[0]->getSequence(), $sequenceEvaluations, true);
            }
        }

        throw new NotFoundHttpException('No sequence evaluation found.');
    }

    /**
     * Regenerate all the certificates of a sequence.
     */
    #[Route(path: '/{sequence}/regenerate', name: 'apiv2_sequence_regenerate_all_certificates', methods: ['PUT'])]
    public function regenerateAllAction(
        #[MapEntity(mapping: ['sequence' => 'uuid'])]
        Sequence $sequence
    ): JsonResponse {
        $this->checkPermission('EDIT', $sequence, [], true);

        $sequenceEvaluations = $this->om->getRepository(SequenceEvaluation::class)->findBy(['sequence' => $sequence]);
        foreach ($sequenceEvaluations as $sequenceEvaluation) {
            if (in_array($sequenceEvaluation->getStatus(), [EvaluationStatus::COMPLETED, EvaluationStatus::PASSED])) {
                $this->certificateManager->getCertificate($sequenceEvaluation, true);
            }
        }

        return new JsonResponse(null, 204);
    }

    private function downloadCertificates(Sequence $sequence, array $sequenceEvaluations, ?bool $regenerate = false): BinaryFileResponse
    {
        if (empty($sequenceEvaluations)) {
            throw new NotFoundHttpException('No sequence evaluation found.');
        }

        $certificateFiles = [];
        foreach ($sequenceEvaluations as $evaluation) {
            if (in_array($evaluation->getStatus(), [EvaluationStatus::COMPLETED, EvaluationStatus::PASSED]) && $this->checkPermission('OPEN', $evaluation)) {
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
                'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($sequence->getName()).'.zip',
            ]);
        }

        return new BinaryFileResponse($certificateFiles[0], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename='.TextNormalizer::toKey($sequence->getName()).'.pdf',
        ]);
    }
}
