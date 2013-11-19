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
     *     "templating" = @DI\Inject("templating")
     * })
     */
    public function __construct($fileDir, $templating)
    {
        $this->fileDir = $fileDir;
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("play_file_video")
     */
    public function onOpenVideo(PlayFileEvent $event)
    {
        $path = $this->fileDir . DIRECTORY_SEPARATOR . $event->getResource()->getHashName();
        $content = $this->templating->render(
            'ClarolineVideoPlayerBundle::video.html.twig',
            array(
                'workspace' => $event->getResource()->getResourceNode()->getWorkspace(),
                'path' => $path,
                'video' => $event->getResource(),
                '_resource' => $event->getResource()
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
