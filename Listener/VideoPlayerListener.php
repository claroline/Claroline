<?php

namespace Claroline\VideoPlayerBundle\Listener;

use Claroline\CoreBundle\Event\Event\PlayFileEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class VideoPlayerListener extends ContainerAware
{
    public function onOpenVideo(PlayFileEvent $event)
    {
        $path = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $event->getResource()->getHashName();
        $content = $this->container->get('templating')->render(
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
