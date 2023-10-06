<?php

namespace Claroline\AppBundle\Manager;

use Claroline\AppBundle\Manager\File\TempFileManager;
use Dompdf\Dompdf;
use Twig\Environment;

class PdfManager
{
    public function __construct(
        private Environment $templating,
        private TempFileManager $tempFileManager,
        private PlatformManager $platformManager
    ) {
    }

    public function fromHtml(string $htmlContent, string $title = null): ?string
    {
        $domPdf = new Dompdf([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'tempDir' => $this->tempFileManager->getDirectory(),
            'fontDir' => $this->tempFileManager->getDirectory(),
            'fontCache' => $this->tempFileManager->getDirectory(),
        ]);

        $domPdf->setBasePath($this->platformManager->getUrl().'/');

        $domPdf->loadHtml($this->templating->render('@ClarolineApp/external.html.twig', [
            'baseUrl' => $this->platformManager->getUrl(),
            'title' => $title,
            'content' => $htmlContent,
        ]));

        // Render the HTML as PDF
        $domPdf->render();

        return $domPdf->output();
    }
}
