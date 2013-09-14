<?php

namespace Claroline\ImagePlayerBundle\Listener;

use Claroline\CoreBundle\Event\PlayFileEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;

class ImagePlayerListener extends ContainerAware
{
    public function onOpenImage(PlayFileEvent $event)
    {
        $images = $this->container->get('claroline.manager.resource_manager')->getByMimeTypeAndParent(
            'image',
            $event->getResource()->getResourceNode()->getParent(),
            $this->container->get('security.context')->getToken()->getUser()->getRoles()
        );

        $path = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $event->getResource()->getHashName();
        $content = $this->container->get('templating')->render(
            'ClarolineImagePlayerBundle::image.html.twig',
            array(
                'workspace' => $event->getResource()->getResourceNode()->getWorkspace(),
                'path' => $path,
                'image' => $event->getResource(),
                '_resource' => $event->getResource(),
                'images' => $images
            )
        );

        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
