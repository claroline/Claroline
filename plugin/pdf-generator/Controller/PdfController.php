<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PdfGeneratorBundle\Controller;

use Claroline\PdfGeneratorBundle\Entity\Pdf;
use Claroline\PdfGeneratorBundle\Manager\PdfManager;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfController
{
    private $pdfManager;

    /**
     * @InjectParams({
     *     "pdfManager" = @Inject("claroline.manager.pdf_manager")
     * })
     */
    public function __construct(PdfManager $pdfManager)
    {
        $this->pdfManager = $pdfManager;
    }

    /**
     * @EXT\Route(
     *     "/download/{pdf}",
     *     name="claro_pdf_download",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "pdf",
     *     class="ClarolinePdfGeneratorBundle:Pdf",
     *     options={"mapping": {"pdf": "guid"}}
     * )
     *
     * @param array $nodes
     * @param bool  $forceArchive
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(Pdf $pdf)
    {
        $response = new StreamedResponse();
        $file = $this->pdfManager->getFile($pdf);

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename='.urlencode($pdf->getName().'.pdf'));
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Connection', 'close');
        $response->send();
    }
}
