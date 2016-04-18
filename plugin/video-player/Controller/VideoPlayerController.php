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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Claroline\VideoPlayerBundle\Form\PlayersType;

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
        $path = $this->container->getParameter('claroline.param.files_directory')
            .DIRECTORY_SEPARATOR
            .$video->getHashName();

        $response = new BinaryFileResponse($path);
        $response->headers->set('Content-Type', $node->getMimeType());

        return $response;
    }

    /**
     * @SEC\PreAuthorize("canOpenAdminTool('platform_packages')")
     * @Route(
     *     "/admin/player/form",
     *     name="claro_video_player_admin_form"
     * )
     * @EXT\Template("ClarolineVideoPlayerBundle:Administration:adminOpen.html.twig")
     */
    public function adminOpenAction()
    {
        $player = $this->get('claroline.config.platform_config_handler')
            ->getParameter('video_player');
        if ($player === null) {
            $player = 'mediaelement';
        }

        $form = $this->get('form.factory')->create(new PlayersType($player));

        return array('form' => $form->createView());
    }

    /**
     * @SEC\PreAuthorize("canOpenAdminTool('platform_packages')")
     * @Route(
     *     "/admin/player/submit",
     *     name="claro_video_player_admin_submit"
     * )
     */
    public function adminSubmitAction()
    {
        $form = $this->get('form.factory')->create(new PlayersType());
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $this->get('claroline.config.platform_config_handler')->setParameter('video_player', $form->get('player')->getData());

            return $this->redirect($this->generateUrl('claro_admin_plugins'));
        }

        return array('form_group' => $form->createView());
    }
}
