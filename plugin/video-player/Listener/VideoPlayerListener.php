<?php

namespace Claroline\VideoPlayerBundle\Listener;

use Claroline\CoreBundle\Event\InjectJavascriptEvent;
use Claroline\CoreBundle\Event\PlayFileEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\ScormBundle\Event\ExportScormResourceEvent;
use Claroline\VideoPlayerBundle\Entity\Track;
use Claroline\VideoPlayerBundle\Manager\VideoPlayerManager;
use Claroline\VideoPlayerBundle\Serializer\TrackSerializer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @DI\Service("claroline.listener.video_player_listener")
 */
class VideoPlayerListener
{
    /**
     * @var PlatformConfigurationHandler
     */
    private $ch;

    /**
     * @var string
     */
    private $fileDir;

    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * @var HttpKernelInterface
     */
    private $httpKernel;

    /**
     * @var RequestStack
     */
    private $request;

    /**
     * @var TrackSerializer
     */
    private $trackSerializer;

    /**
     * @var VideoPlayerManager
     */
    private $manager;

    /**
     * VideoPlayerListener constructor.
     *
     * @DI\InjectParams({
     *     "fileDir"         = @DI\Inject("%claroline.param.files_directory%"),
     *     "templating"      = @DI\Inject("templating"),
     *     "ch"              = @DI\Inject("claroline.config.platform_config_handler"),
     *     "httpKernel"      = @DI\Inject("http_kernel"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "trackSerializer" = @DI\Inject("claroline.serializer.video.track"),
     *     "manager"         = @DI\Inject("claroline.manager.video_player_manager")
     * })
     *
     * @param string                       $fileDir
     * @param TwigEngine                   $templating
     * @param PlatformConfigurationHandler $ch
     * @param HttpKernelInterface          $httpKernel
     * @param RequestStack                 $requestStack
     * @param TrackSerializer              $trackSerializer
     * @param VideoPlayerManager           $manager
     */
    public function __construct(
        $fileDir,
        TwigEngine $templating,
        PlatformConfigurationHandler $ch,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        TrackSerializer $trackSerializer,
        VideoPlayerManager $manager
    ) {
        $this->fileDir = $fileDir;
        $this->templating = $templating;
        $this->ch = $ch;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->trackSerializer = $trackSerializer;
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("play_file_video")
     * @DI\Observe("play_file_audio")
     *
     * @param PlayFileEvent $event
     */
    public function onOpen(PlayFileEvent $event)
    {
        $tracks = $this->manager->getTracksByVideo($event->getResource());

        $content = $this->templating->render(
            'ClarolineVideoPlayerBundle::video.html.twig',
            [
                'workspace' => $event->getResource()->getResourceNode()->getWorkspace(),
                '_resource' => $event->getResource(),
                'tracks' => array_map(function (Track $track) {
                    return $this->trackSerializer->serialize($track);
                }, $tracks),
            ]
        );

        $event->setResponse(new Response($content));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("plugin_options_videoplayerbundle")
     *
     * @param PluginOptionsEvent $event
     */
    public function onOpenAdministration(PluginOptionsEvent $event)
    {
        $params = ['_controller' => 'ClarolineVideoPlayerBundle:VideoPlayer:AdminOpen'];
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("inject_javascript_layout")
     *
     * @param InjectJavascriptEvent $event
     */
    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $content = $this->templating->render('ClarolineVideoPlayerBundle::scripts.html.twig', []);

        $event->addContent($content);
    }

    /**
     * @DI\Observe("export_scorm_file_video")
     * @DI\Observe("export_scorm_file_audio")
     *
     * @param ExportScormResourceEvent $event
     */
    public function onExportScorm(ExportScormResourceEvent $event)
    {
        $resource = $event->getResource();

        $template = $this->templating->render(
            'ClarolineVideoPlayerBundle:Scorm:export.html.twig', [
                '_resource' => $resource,
                'tracks' => $this->manager->getTracksByVideo($resource),
            ]
        );

        // Set export template
        $event->setTemplate($template);

        // Add Image file
        $event->addFile('file_'.$resource->getResourceNode()->getId(), $resource->getHashName());

        $event->stopPropagation();
    }
}
