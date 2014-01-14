<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 *
 */

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Api controller.
 *
 * @Route("/internapi")
 */
class ExporterController extends Controller
{
    /**
     * @Route("/query", name="exporter_api_norewrite", defaults={"entityReference"="null", "_format"="xml"})
     * @Route("/{entityReference}.{_format}", name="exporter_api", defaults={"_format"="xml"})
     */
    public function indexAction(Request $request, $entityReference)
    {
        $format = $request->getRequestFormat();
        if($request->query->has('format')) {
            $format = $request->query->get('format');
        }

        if($entityReference == "null") {
            $entityReference = $request->query->get('entityReference');
        }

        $entities = $this->get('idci_exporter.manager')->extract(
            $entityReference,
            $request->query->all()
        );

        $export = $this->get('idci_exporter.manager')->export(
            $entities,
            $format,
            $request->query->all()
        );

        $response = new Response();
        $response->setContent($export->getContent());
        $response->headers->set('Content-Type', $export->getContentType());

        return $response;
    }
}
