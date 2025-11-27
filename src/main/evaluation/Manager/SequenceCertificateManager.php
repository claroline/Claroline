<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Manager\File\ArchiveManager;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\EvaluationBundle\Entity\Certificate\SequenceCertificate;
use Claroline\EvaluationBundle\Entity\UserEvaluation\SequenceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;

class SequenceCertificateManager
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly PdfManager $pdfManager,
        private readonly TemplateManager $templateManager,
        private readonly LocaleManager $localeManager,
        private readonly FileManager $fileManager,
        private readonly PlatformManager $platformManager,
        private readonly TempFileManager $tempFileManager,
        private readonly ArchiveManager $archiveManager,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getCertificate(SequenceEvaluation $evaluation, bool $regenerate = false): ?string
    {
        $sequence = $evaluation->getSequence();

        $certificate = $this->om->getRepository(SequenceCertificate::class)->findOneBy([
            'evaluation' => $evaluation,
            'user' => $evaluation->getUser(),
        ], ['issueDate' => 'DESC']);

        if ($certificate && !$regenerate) {
            return $this->getCertificateFile($certificate);
        }

        $certificate = new SequenceCertificate();
        $certificate->setUser($evaluation->getUser());
        $certificate->setIssueDate(new \DateTime());
        $certificate->setEvaluation($evaluation);
        $certificate->setObtentionDate($evaluation->getEndedAt() ?? $evaluation->getLastActivityAt());
        $certificate->setScore($evaluation->getScore() ?: 0);
        $certificate->setStatus($evaluation->getStatus());

        $placeholders = $this->getPlaceholders($certificate);

        $locale = $evaluation->getUser()->getLocale();
        if (!$locale) {
            $locale = $this->localeManager->getDefault();
        }

        if ($sequence->getCertificateTemplate()) {
            $html = $this->templateManager->getTemplateContent(
                $sequence->getCertificateTemplate(),
                $placeholders,
                $locale
            );
        } else {
            $html = $this->templateManager->getTemplate(
                EvaluationStatus::PASSED === $evaluation->getStatus() ? 'evaluation_success_certificate' : 'evaluation_participation_certificate',
                $placeholders,
                $locale
            );
        }

        $certificate->setLanguage($locale);
        $certificate->setContent($html);

        $this->om->persist($certificate);
        $this->om->flush();

        return $this->getCertificateFile($certificate);
    }

    public function createArchive(array $certificateFiles): string
    {
        $tmpFile = $this->tempFileManager->generate();

        $archive = $this->archiveManager->create($tmpFile, new FileBag());
        foreach ($certificateFiles as $certificate) {
            $archive->addFromString(basename($certificate), file_get_contents($certificate));
        }
        $archive->close();

        return $tmpFile;
    }

    public function getCertificateFile(SequenceCertificate $certificate): string
    {
        $path = $this->getCertificateFilepath($certificate);

        $filesystem = new Filesystem();
        if (!$filesystem->exists($path)) {
            $filesystem->dumpFile($path, $this->pdfManager->fromHtml($certificate->getContent()));
        }

        return $path;
    }

    public function removeCertificateFile(SequenceCertificate $certificate): void
    {
        $path = $this->getCertificateFilepath($certificate);

        $filesystem = new Filesystem();
        if ($filesystem->exists($path)) {
            $filesystem->remove($path);
        }
    }

    private function getCertificateFilepath(SequenceCertificate $certificate): string
    {
        $path = $this->fileManager->getDirectory();
        $path .= DIRECTORY_SEPARATOR.'sequence_certificates';
        $path .= DIRECTORY_SEPARATOR.$certificate->getEvaluation()->getSequence()->getUuid();
        $path .= DIRECTORY_SEPARATOR.$certificate->getUuid().'.pdf';

        return $path;
    }

    private function getPlaceholders(SequenceCertificate $certificate): array
    {
        $evaluation = $certificate->getEvaluation();
        $sequence = $evaluation->getSequence();
        $user = $evaluation->getUser();

        $score = $evaluation->getScore() ?: 0;
        $scoreMax = $evaluation->getScoreMax() ?: 1;
        $finalScore = round(($score / $scoreMax) * 100, 2);

        return array_merge([
            'evaluated_content_name' => $sequence->getName(),
            'evaluated_content_code' => $sequence->getCode(),
            'evaluated_content_description' => $sequence->getDescription(),
            'evaluated_content_poster' => $sequence->getPoster() ? '<img src="'.$this->platformManager->getUrl().'/'.$sequence->getPoster().'" style="max-width: 100%;"/>' : '',

            'user_first_name' => $user->getFirstName(),
            'user_last_name' => $user->getLastName(),
            'user_username' => $user->getUsername(),

            'evaluation_score' => $finalScore ?: '0',
            'evaluation_score_max' => 100,
            'evaluation_duration' => round($evaluation->getDuration() / 60, 2), // in minutes
            'evaluation_status' => $this->translator->trans('evaluation_'.$evaluation->getStatus().'_status', [], 'evaluation'),
        ],
            $this->templateManager->formatDatePlaceholder('evaluation_last_activity', $evaluation->getLastActivityAt()),
            $this->templateManager->formatDatePlaceholder('evaluation_start', $evaluation->getStartedAt()),
            $this->templateManager->formatDatePlaceholder('evaluation_end', $evaluation->getEndedAt()),
            $this->templateManager->formatDatePlaceholder('certificate_issued', $certificate->getIssueDate()),
            $this->templateManager->formatDatePlaceholder('certificate_obtention', $certificate->getObtentionDate())
        );
    }
}
