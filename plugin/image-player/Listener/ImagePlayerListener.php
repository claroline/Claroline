<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            $this->container->get('security.token_storage')->getToken()->getRoles()
        );

        $path = $this->container->getParameter('claroline.param.files_directory')
            .DIRECTORY_SEPARATOR
            .$event->getResource()->getHashName();
        $content = $this->container->get('templating')->render(
            'ClarolineImagePlayerBundle::image.html.twig',
            array(
                'path' => $path,
                'image' => $event->getResource(),
                '_resource' => $event->getResource(),
                'images' => $images,
            )
        );

        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
