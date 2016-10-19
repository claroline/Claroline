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

use Claroline\CoreBundle\Event\InjectJavascriptEvent;
use Claroline\CoreBundle\Event\PlayFileEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\ScormBundle\Event\ExportScormResourceEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

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
        $authorization = $this->container->get('security.authorization_checker');
        $collection = new ResourceCollection([$event->getResource()->getResourceNode()]);
        $canExport = $authorization->isGranted('EXPORT', $collection);
        $path = $this->fileDir.DIRECTORY_SEPARATOR.$event->getResource()->getHashName();
        $content = $this->templating->render(
            'ClarolineVideoPlayerBundle::video.html.twig',
            [
                'workspace' => $event->getResource()->getResourceNode()->getWorkspace(),
                'path' => $path,
                'video' => $event->getResource(),
                '_resource' => $event->getResource(),
                'tracks' => $this->container->get('claroline.manager.video_player_manager')->getTracksByVideo($event->getResource()),
                'canExport' => $canExport,
            ]
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
        $params = ['_controller' => 'ClarolineVideoPlayerBundle:VideoPlayer:AdminOpen'];
        $subRequest = $request->duplicate([], null, $params);
        $response = $httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("inject_javascript_layout")
     *
     * @param InjectJavascriptEvent $event
     *
     * @return string
     */
    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $content = $this->templating->render('ClarolineVideoPlayerBundle::scripts.html.twig', []);
        $event->addContent($content);
    }

    /**
     * @param ExportScormResourceEvent $event
     *
     * @DI\Observe("export_scorm_file_video")
     * @DI\Observe("export_scorm_file_audio")
     */
    public function onExportScorm(ExportScormResourceEvent $event)
    {
        $resource = $event->getResource();

        $template = $this->container->get('templating')->render(
            'ClarolineVideoPlayerBundle:Scorm:export.html.twig', [
                '_resource' => $resource,
                'tracks' => $this->container->get('claroline.manager.video_player_manager')->getTracksByVideo($resource),
            ]
        );

        // Set export template
        $event->setTemplate($template);

        // Add Image file
        $event->addFile('file_'.$resource->getResourceNode()->getId(), $resource->getHashName());

        $event->stopPropagation();
    }
}
