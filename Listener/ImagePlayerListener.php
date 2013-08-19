<?php

namespace Claroline\ImagePlayerBundle\Listener;

use Claroline\CoreBundle\Event\Event\PlayFileEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class ImagePlayerListener extends ContainerAware
{
    public function onOpenImage(PlayFileEvent $event)
    {
        $path = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $event->getResource()->getHashName();
        $content = $this->container->get('templating')->render(
            'ClarolineImagePlayerBundle::image.html.twig',
            array(
                'workspace' => $event->getResource()->getResourceNode()->getWorkspace(),
                'path' => $path,
                'image' => $event->getResource(),
                '_resource' => $event->getResource()
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}