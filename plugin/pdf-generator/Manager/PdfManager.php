<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PdfGeneratorBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\ForumBundle\Repository\PdfRepository;
use Claroline\PdfGeneratorBundle\Entity\Pdf;
use JMS\DiExtraBundle\Annotation as DI;
use Knp\Snappy\Pdf as PdfSnappy;

/**
 * @DI\Service("claroline.manager.pdf_manager")
 */
class PdfManager
{
    /** @var PdfRepository */
    private $repo;

    /**
     * @DI\InjectParams({
     *     "om"     = @DI\Inject("claroline.persistence.object_manager"),
     *     "snappy" = @DI\Inject("knp_snappy.pdf"),
     *     "pdfDir" = @DI\Inject("%claroline.param.pdf_directory%"),
     *     "ut"     = @DI\Inject("claroline.utilities.misc")
     * })
     */
    public function __construct(
        ObjectManager $om,
        PdfSnappy $snappy,
        $pdfDir,
        $ut
    ) {
        $this->om = $om;
        $this->snappy = $snappy;
        $this->pdfDir = $pdfDir;
        $this->ut = $ut;
        $this->repo = $om->getRepository('ClarolinePdfGeneratorBundle:Pdf');
    }

    public function create($html, $name, User $creator, $subFolder = 'main')
    {
        $ds = DIRECTORY_SEPARATOR;
        $guid = $this->ut->generateGuid();
        $path = $subFolder.$ds.$guid.'.pdf';
        $pdf = new Pdf();

        $pdf->setName($name);
        $pdf->setGuid($guid);
        $pdf->setPath($path);
        $pdf->setCreator($creator);

        if (file_exists($this->pdfDir.$ds.$path)) {
            $realpath = realpath($this->pdfDir.$ds.$path);
            throw new \Exception("The path {$realpath} is already taken by an other file ! aborted.");
        }

        @mkdir($this->pdfDir);
        @mkdir($this->pdfDir.$ds.$subFolder);

        $this->snappy->generateFromHtml($html, $this->pdfDir.$ds.$path);
        $this->om->persist($pdf);
        $this->om->flush();

        return $pdf;
    }

    public function getFile(Pdf $pdf)
    {
        $ds = DIRECTORY_SEPARATOR;

        return realpath($this->pdfDir.$ds.$pdf->getPath());
    }

    /**
     * Find all content for a given user and the replace him by another.
     *
     * @param User $from
     * @param User $to
     *
     * @return int
     */
    public function replaceUser(User $from, User $to)
    {
        $pdfs = $this->repo->findByCreator($from);

        if (count($pdfs) > 0) {
            foreach ($pdfs as $pdf) {
                $pdf->setCreator($to);
            }

            $this->om->flush();
        }

        return count($pdfs);
    }
}
