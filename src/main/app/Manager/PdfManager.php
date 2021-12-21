<?php

namespace Claroline\AppBundle\Manager;

use Claroline\AppBundle\Manager\File\TempFileManager;
use Dompdf\Dompdf;
use Twig\Environment;

class PdfManager
{
    /** @var Environment */
    private $templating;
    /** @var TempFileManager */
    private $tempFileManager;
    /** @var PlatformManager */
    private $platformManager;

    public function __construct(
        Environment $templating,
        TempFileManager $tempFileManager,
        PlatformManager $platformManager
    ) {
        $this->templating = $templating;
        $this->tempFileManager = $tempFileManager;
        $this->platformManager = $platformManager;
    }

    public function fromHtml(string $htmlContent): ?string
    {
        $domPdf = new Dompdf([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'tempDir' => $this->tempFileManager->getDirectory(),
            'fontDir' => $this->tempFileManager->getDirectory(),
            'fontCache' => $this->tempFileManager->getDirectory(),
        ]);

        $domPdf->loadHtml($this->templating->render('@ClarolineApp/pdf.html.twig', [
            'baseUrl' => $this->platformManager->getUrl(),
            'content' => $htmlContent,
        ]));

        // Render the HTML as PDF
        $domPdf->render();

        return $domPdf->output();
    }
}
