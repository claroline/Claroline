<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TextPlayerBundle\Listener;

use Claroline\CoreBundle\Event\PlayFileEvent;
use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *  @DI\Service()
 */
class TextPlayerListener
{
    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @DI\Observe("play_file_text")
     *
     * @param PlayFileEvent $event
     */
    public function onOpenText(PlayFileEvent $event)
    {
        $path = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $event->getResource()->getHashName();
        $text = file_get_contents($path);
        $content = $this->container->get('templating')->render(
            'ClarolineTextPlayerBundle::text.html.twig',
            array(
                'path' => $path,
                'text' => $text,
                '_resource' => $event->getResource(),
            )
        );

        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
