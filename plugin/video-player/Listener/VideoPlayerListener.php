<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\VideoPlayerBundle\Listener;

use Claroline\CoreBundle\Event\PlayFileEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Claroline\CoreBundle\Event\PluginOptionsEvent;

/**
 * @DI\Service("claroline.listener.video_player_listener")
 */
class VideoPlayerListener extends ContainerAware
{
    private $fileDir;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "fileDir" = @DI\Inject("%claroline.param.files_directory%"),
     *     "templating" = @DI\Inject("templating"),
     *     "ch" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($fileDir, $templating, $ch, $container)
    {
        $this->fileDir = $fileDir;
        $this->templating = $templating;
        $this->ch = $ch;
        $this->container = $container;
    }

    /**
     * @DI\Observe("play_file_video")
     * @DI\Observe("play_file_audio")
     */
    public function onOpenVideo(PlayFileEvent $event)
    {
        $player = $this->ch->getParameter('video_player');
        if ($player == null) {
            $player = 'videojs';
        }

        $path = $this->fileDir.DIRECTORY_SEPARATOR.$event->getResource()->getHashName();
        $content = $this->templating->render(
            'ClarolineVideoPlayerBundle::video.html.twig',
            array(
                'workspace' => $event->getResource()->getResourceNode()->getWorkspace(),
                'path' => $path,
                'video' => $event->getResource(),
                '_resource' => $event->getResource(),
                'player' => $player,
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("plugin_options_videoplayerbundle")
     */
    public function onOpenAdministration(PluginOptionsEvent $event)
    {
        $requestStack = $this->container->get('request_stack');
        $httpKernel = $this->container->get('http_kernel');
        $request = $requestStack->getCurrentRequest();
        $params = array('_controller' => 'ClarolineVideoPlayerBundle:VideoPlayer:AdminOpen');
        $subRequest = $request->duplicate(array(), null, $params);
        $response = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
