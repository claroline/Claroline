<?php

namespace Claroline\ImagePlayerBundle\Listener;

use Claroline\CoreBundle\Library\Resource\Event\PlayFileEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class ImagePlayerListener extends ContainerAware
{
    public function onOpenImage (PlayFileEvent $event)
    {
        $path = $this->container->getParameter('claroline.files.directory').DIRECTORY_SEPARATOR.$event->getResource()->getHashName();
        $content = $this->container->get('templating')
            ->render('ClarolineImagePlayerBundle::image.html.twig',
                array('workspace' => $event->getResource()->getWorkspace(), 'path' => $path, 'image' => $event->getResource()));
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}