<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\VideoPlayerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

//todo use sf2.2 BinaryFileResponse
class VideoPlayerController extends Controller
{
    /**
     * @Route(
     *     "/stream/video/{node}/{name}",
     *     name="claro_stream_video"
     * )
     */
    public function streamAction(ResourceNode $node, $name)
    {
        $video = $this->get('claroline.manager.resource_manager')->getResourceFromNode($node);
        $response = new StreamedResponse();
        $path = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $video->getHashName();
        $response->setCallBack(
            function () use ($path) {
                readfile($path);
            }
        );

        $response->headers->set('Content-Type', $node->getMimeType());

        return $response->send();
    }
}
