<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PdfPlayerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class PdfPlayerController extends Controller
{
    /**
     * @Route(
     *     "/pdf/{node}",
     *     name="claro_pdf",
     *     options = {"expose"=true}
     * )
     */
    public function fileAction(ResourceNode $node)
    {
        $pdf = $this->get('claroline.manager.resource_manager')->getResourceFromNode($node);

        $response = new StreamedResponse();
        $path = $this->container->getParameter(
            'claroline.param.files_directory'
        ).DIRECTORY_SEPARATOR.$pdf->getHashName();
        $response->setCallBack(
            function () use ($path) {
                readfile($path);
            }
        );
        $response->headers->set('Content-Type', $node->getMimeType());
        
        return $response->send();
    }
}
