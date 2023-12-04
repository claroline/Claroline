<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Manager\File\ArchiveManager;
use Claroline\AppBundle\Manager\PdfManager as BasePdfManager;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class PdfManager
{
    /** @var TranslatorInterface */
    private $translator;
    /** @var PlatformManager */
    private $platformManager;
    /** @var BasePdfManager */
    private $pdfManager;
    /** @var TemplateManager */
    private $templateManager;
    /** @var ArchiveManager */
    private $archiveManager;

    public function __construct(
        TranslatorInterface $translator,
        PlatformManager $platformManager,
        BasePdfManager $pdfManager,
        TemplateManager $templateManager,
        ArchiveManager $archiveManager
    ) {
        $this->translator = $translator;
        $this->platformManager = $platformManager;
        $this->pdfManager = $pdfManager;
        $this->templateManager = $templateManager;
        $this->archiveManager = $archiveManager;
    }

    public function getWorkspaceParticipationCertificate(Evaluation $evaluation): ?string
    {
        // only generate certificate if the evaluation is finished
        if (!$evaluation->isTerminated()) {
            return null;
        }

        $placeholders = $this->getCommonPlaceholders($evaluation);

        return $this->pdfManager->fromHtml(
            $this->templateManager->getTemplate('workspace_participation_certificate', $placeholders, $evaluation->getUser()->getLocale())
        );
    }

    public function getWorkspaceSuccessCertificate(Evaluation $evaluation): ?string
    {
        // only generate certificate if the evaluation is finished and has success/failure status
        if (!$evaluation->isTerminated() || !in_array($evaluation->getStatus(), [AbstractEvaluation::STATUS_PASSED, AbstractEvaluation::STATUS_FAILED])) {
            return null;
        }

        $score = $evaluation->getScore() ?: 0;
        $scoreMax = $evaluation->getScoreMax() ?: 1;
        $finalScore = round(($score / $scoreMax) * 100, 2);

        $placeholders = array_merge($this->getCommonPlaceholders($evaluation), [
            'evaluation_score' => $finalScore ?: '0',
            'evaluation_score_max' => 100,
        ]);

        return $this->pdfManager->fromHtml(
            $this->templateManager->getTemplate('workspace_success_certificate', $placeholders, $evaluation->getUser()->getLocale())
        );
    }

    private function getCommonPlaceholders(Evaluation $evaluation): array
    {
        $workspace = $evaluation->getWorkspace();
        $user = $evaluation->getUser();

        return array_merge([
            'workspace_name' => $workspace->getName(),
            'workspace_code' => $workspace->getCode(),
            'workspace_description' => $workspace->getDescription(),
            'workspace_poster' => $workspace->getPoster() ? '<img src="'.$this->platformManager->getUrl().'/'.$workspace->getPoster().'" style="max-width: 100%;"/>' : '',

            'user_first_name' => $user->getFirstName(),
            'user_last_name' => $user->getLastName(),
            'user_username' => $user->getUsername(),

            'evaluation_duration' => round($evaluation->getDuration() / 60, 2), // in minutes
            'evaluation_status' => $this->translator->trans('evaluation_'.$evaluation->getStatus().'_status', [], 'workspace'),
        ], $this->templateManager->formatDatePlaceholder('evaluation', $evaluation->getDate()));
    }

    public function getWorkspaceParticipationCertificates(array $evaluations): array
    {
        $certificates = [];
        foreach ($evaluations as $evaluation) {
            $certificate = $this->getWorkspaceParticipationCertificate($evaluation);
            if ($certificate) {
                $certificates[] = $certificate;
            }
        }

        return $certificates;
    }

    public function getWorkspaceSuccessCertificates(array $evaluations): \ZipArchive|string|null
    {
        $archive = $this->archiveManager->create(null, new FileBag());

        if (1 === count($evaluations)) {
            return $this->getWorkspaceSuccessCertificate($evaluations[0]);
        }

        foreach ($evaluations as $workspaceEvaluation) {
            $certificate = $this->getWorkspaceSuccessCertificate($workspaceEvaluation);
            if ($certificate) {
                $workspace = $workspaceEvaluation->getWorkspace();
                $user = $workspaceEvaluation->getUser();
                $archive->addFromString($workspace->getName().'-'.TextNormalizer::toKey($user->getFullName()).'-success.pdf', $certificate);
            }
        }

        $archive->close();

        return $archive;
    }

    public function getWorkspaceCertificates($evaluations, bool $onlySuccessful): \ZipArchive|string|null
    {
        $certificates = [];

        foreach ($evaluations as $workspaceEvaluation) {
            if ($onlySuccessful) {
                $certificate = $this->getWorkspaceSuccessCertificate($workspaceEvaluation);
            } else {
                $certificate = $this->getWorkspaceParticipationCertificate($workspaceEvaluation);
            }

            if ($certificate) {
                $certificates[] = [
                    'certificate' => $certificate,
                    'workspace' => $workspaceEvaluation->getWorkspace(),
                    'user' => $workspaceEvaluation->getUser(),
                ];
            }
        }

        if (0 === count($certificates)) {
            if ($onlySuccessful) {
                throw new NotFoundHttpException('No success certificates are available yet.');
            } else {
                throw new NotFoundHttpException('No participation certificates are available yet.');
            }
        } elseif (1 === count($certificates)) {
            return $certificates[0]['certificate'];
        }

        $archive = $this->archiveManager->create(null, new FileBag());

        foreach ($certificates as $certificateData) {
            $certificate = $certificateData['certificate'];
            $workspace = $certificateData['workspace'];
            $user = $certificateData['user'];

            if ($onlySuccessful) {
                $archive->addFromString($workspace->getName().'-'.TextNormalizer::toKey($user->getFullName()).'-success.pdf', $certificate);
            } else {
                $archive->addFromString($workspace->getName().'-'.TextNormalizer::toKey($user->getFullName()).'-participation.pdf', $certificate);
            }
        }

        if (0 === $archive->numFiles) {
            if ($onlySuccessful) {
                throw new NotFoundHttpException('Cannot add success certificates to archive.');
            } else {
                throw new NotFoundHttpException('Cannot add participation certificates to archive.');
            }
        }

        return $archive;
    }
}
