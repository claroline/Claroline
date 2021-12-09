<?php

namespace Claroline\AppBundle\Manager;

use Dompdf\Dompdf;
use Twig\Environment;

class PdfManager
{
    /** @var Environment */
    private $templating;
    /** @var PlatformManager */
    private $platformManager;

    public function __construct(Environment $templating, PlatformManager $platformManager)
    {
        $this->platformManager = $platformManager;
        $this->templating = $templating;
    }

    public function fromHtml(string $htmlContent): ?string
    {
        $domPdf = new Dompdf([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
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
