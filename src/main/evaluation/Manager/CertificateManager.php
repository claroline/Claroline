<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\AppBundle\Manager\PdfManager as BasePdfManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\CoreBundle\Manager\Template\TemplateManager;
use Claroline\EvaluationBundle\Entity\Certificate;
use Symfony\Component\HttpFoundation\File\File;

class CertificateManager
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly BasePdfManager $basePdfManager,
        private readonly TemplateManager $templateManager,
        private readonly FileManager $fileManager,
        private readonly PdfManager $pdfManager
    ) {
    }

    public function generateCertificate(Evaluation $evaluation): ?string
    {
        if (!$evaluation->isTerminated()) {
            return null;
        }

        $placeholders = $this->pdfManager->getCommonPlaceholders($evaluation);

        $html = $this->templateManager->getTemplate(
            'workspace_evaluation_certificate',
            $placeholders,
            $evaluation->getUser()->getLocale()
        );

        $pdf = new File($this->basePdfManager->fromHtml($html));

        $certificate = new Certificate();
        $certificate->setUser($evaluation->getUser());
        $certificate->setIssueDate(new \DateTime());
        $certificate->setEvaluation($evaluation);
        $certificate->setObtentionDate($evaluation->getDate());
        $certificate->setScore($evaluation->getScore());
        $certificate->setLanguage($evaluation->getUser()->getLocale());
        $certificate->setStatus($evaluation->getStatus());
        $certificate->setContent($html);
        $this->om->persist($certificate);
        $this->om->flush();

        $path = $this->fileManager->getDirectory();
        $path .= DIRECTORY_SEPARATOR.'certificates';
        $path .= DIRECTORY_SEPARATOR.$evaluation->getWorkspace()->getUuid();
        $path .= DIRECTORY_SEPARATOR.$certificate->getUuid().'.pdf';

        $publicFile = $this->fileManager->createFile($pdf, $path);

        return $publicFile->getUrl();
    }
}
