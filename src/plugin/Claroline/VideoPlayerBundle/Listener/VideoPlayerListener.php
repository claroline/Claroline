<?php

namespace Claroline\VideoPlayerBundle\Listener;

use Claroline\CoreBundle\Library\Resource\Event\PlayFileEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class VideoPlayerListener extends ContainerAware
{
    public function onOpenVideo(PlayFileEvent $event)
    {
        $path = $this->container->getParameter('claroline.files.directory').DIRECTORY_SEPARATOR.$event->getInstance()->getResource()->getHashName();
        $content = $this->container->get('templating')
            ->render('ClarolineVideoPlayerBundle::video.html.twig',
                array('workspace' => $event->getInstance()->getWorkspace(), 'path' => $path, 'video' => $event->getInstance()->getResource()));
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
