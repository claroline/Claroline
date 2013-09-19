<?php

namespace Claroline\PdfPlayerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class PdfPlayerController extends Controller
{
    /**
     * @Route(
     *     "/pdf/{node}",
     *     name="claro_pdf"
     * )
     */
    public function fileAction(ResourceNode $node)
    {
        $pdf = $this->get('claroline.manager.resource_manager')->getResourceFromNode($node);

        $path = $this->container->getParameter(
            'claroline.param.files_directory'
        ).DIRECTORY_SEPARATOR.$pdf->getHashName();

        return new Response(file_get_contents($path));
    }
}
