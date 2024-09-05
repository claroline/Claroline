<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Manager\File\ArchiveManager;
use Claroline\AppBundle\Manager\File\TempFileManager;
use Claroline\AppBundle\Manager\PdfManager;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\EvaluationBundle\Entity\Certificate;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\Translation\TranslatorInterface;

class CertificateManager
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

    public function getCertificate(Evaluation $evaluation, bool $regenerate = false): ?string
    {
        $certificate = $this->om->getRepository(Certificate::class)->findOneBy([
            'evaluation' => $evaluation,
            'user' => $evaluation->getUser(),
        ]);

        if ($certificate && !$regenerate) {
            if (file_exists($this->getCertificateFilepath($certificate))) {
                return $this->getCertificateFilepath($certificate);
            }
        }

        $placeholders = $this->getCommonPlaceholders($evaluation);

        $locale = $evaluation->getUser()->getLocale();
        if (!$locale) {
            $locale = $this->localeManager->getDefault();
        }

        $html = $this->templateManager->getTemplate(
            $evaluation->isTerminated() ? 'workspace_success_certificate' : 'workspace_participation_certificate',
            $placeholders,
            $locale
        );

        $certificate = new Certificate();
        $certificate->setUser($evaluation->getUser());
        $certificate->setIssueDate(new \DateTime());
        $certificate->setEvaluation($evaluation);
        $certificate->setObtentionDate($evaluation->getDate());
        $certificate->setScore($evaluation->getScore() ?: 0);
        $certificate->setLanguage($locale);
        $certificate->setStatus($evaluation->getStatus());
        $certificate->setContent($html);
        $this->om->persist($certificate);
        $this->om->flush();

        $path = $this->getCertificateFilepath($certificate);

        $filesystem = new Filesystem();
        if (!$filesystem->exists($path)) {
            $filesystem->dumpFile($path, $this->pdfManager->fromHtml($html));
        }

        return $path;
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

    private function getCertificateFilepath(Certificate $certificate): string
    {
        $path = $this->fileManager->getDirectory();
        $path .= DIRECTORY_SEPARATOR.'certificates';
        $path .= DIRECTORY_SEPARATOR.$certificate->getEvaluation()->getWorkspace()->getUuid();
        $path .= DIRECTORY_SEPARATOR.$certificate->getUser()->getUuid().'.pdf';

        return $path;
    }

    private function getCommonPlaceholders(Evaluation $evaluation): array
    {
        $workspace = $evaluation->getWorkspace();
        $user = $evaluation->getUser();

        $score = $evaluation->getScore() ?: 0;
        $scoreMax = $evaluation->getScoreMax() ?: 1;
        $finalScore = round(($score / $scoreMax) * 100, 2);

        return array_merge([
            'workspace_name' => $workspace->getName(),
            'workspace_code' => $workspace->getCode(),
            'workspace_description' => $workspace->getDescription(),
            'workspace_poster' => $workspace->getPoster() ? '<img src="'.$this->platformManager->getUrl().'/'.$workspace->getPoster().'" style="max-width: 100%;"/>' : '',

            'user_first_name' => $user->getFirstName(),
            'user_last_name' => $user->getLastName(),
            'user_username' => $user->getUsername(),

            'evaluation_score' => $finalScore ?: '0',
            'evaluation_score_max' => 100,
            'evaluation_duration' => round($evaluation->getDuration() / 60, 2), // in minutes
            'evaluation_status' => $this->translator->trans('evaluation_'.$evaluation->getStatus().'_status', [], 'workspace'),
        ], $this->templateManager->formatDatePlaceholder('evaluation', $evaluation->getDate()));
    }
}
