<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Manager\File\ArchiveManager;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Manager\PdfManager as BasePdfManager;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Library\Normalizer\TextNormalizer;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Symfony\Contracts\Translation\TranslatorInterface;

class PdfManager
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PlatformManager $platformManager,
        private readonly BasePdfManager $pdfManager,
        private readonly TemplateManager $templateManager,
        private readonly TempFileManager $tempFileManager,
        private readonly ArchiveManager $archiveManager
    ) {
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

    public function getCommonPlaceholders(Evaluation $evaluation): array
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

    public function getWorkspaceParticipationCertificates(array $evaluations): ?array
    {
        $certificates = [];
        foreach ($evaluations as $evaluation) {
            $certificateFilename = TextNormalizer::toKey($evaluation->getWorkspace()->getName().'-'.$evaluation->getUser()->getFullName()).'.pdf';
            $certificates[$certificateFilename] = $this->getWorkspaceParticipationCertificate($evaluation);
        }

        return $this->generateCertificates($certificates);
    }

    public function getWorkspaceSuccessCertificates(array $evaluations): ?array
    {
        $certificates = [];
        foreach ($evaluations as $evaluation) {
            $certificateFilename = TextNormalizer::toKey($evaluation->getWorkspace()->getName().'-'.$evaluation->getUser()->getFullName()).'.pdf';
            $certificates[$certificateFilename] = $this->getWorkspaceSuccessCertificate($evaluation);
        }

        return $this->generateCertificates($certificates);
    }

    private function generateCertificates(array $certificates): ?array
    {
        if (empty($certificates)) {
            return null;
        }

        $tmpFile = $this->tempFileManager->generate();

        if (1 === count($certificates)) {
            $certificateNames = array_keys($certificates);
            file_put_contents($tmpFile, $certificates[$certificateNames[0]]);

            return [$certificateNames[0], $tmpFile];
        }

        $archive = $this->archiveManager->create($tmpFile, new FileBag());
        foreach ($certificates as $certificateName => $certificateData) {
            $archive->addFromString($certificateName, $certificateData);
        }

        $archive->close();

        return [TextNormalizer::toKey('certificates.zip'), $tmpFile];
    }
}
