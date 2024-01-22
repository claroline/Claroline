<?php

namespace Claroline\AppBundle\Manager;

use Claroline\AppBundle\Manager\File\TempFileManager;
use Dompdf\Dompdf;
use Twig\Environment;

class PdfManager
{
    public function __construct(
        private readonly Environment $templating,
        private readonly TempFileManager $tempFileManager,
        private readonly PlatformManager $platformManager
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
            'includeTheme' => false, // DOM PDF only supports CSS 2.1 features, it will break if we load the whole theme here
        ]));

        // Render the HTML as PDF
        $domPdf->render();

        return $domPdf->output();
    }
}
