<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ImagePlayerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class ImagePlayerController extends Controller
{
    /**
     * @Route(
     *     "/image/{node}",
     *     name="claro_image"
     * )
     */
    public function fileAction(ResourceNode $node)
    {
        $image = $this->get('claroline.manager.resource_manager')->getResourceFromNode($node);

        $response = new StreamedResponse();
        $path = $this->container->getParameter(
            'claroline.param.files_directory'
        ).DIRECTORY_SEPARATOR.$image->getHashName();
        $response->setCallBack(
            function () use ($path) {
                readfile($path);
            }
        );
        $response->headers->set('Content-Type', $node->getMimeType());

        return $response->send();
    }
}
