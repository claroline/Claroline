<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace [[Vendor]]\[[Bundle]]Bundle\Listener;

use Claroline\CoreBundle\Event\PlayFileEvent;
use Symfony\Component\HttpFoundation\Response;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *  @DI\Service()
 */
class [[File_Mime]]PlayerListener
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
     * @DI\Observe("play_file_[[file_mime]]")
     *
     * @param PlayFileEvent $event
     */
    public function onOpen[[File_Mime]](PlayFileEvent $event)
    {
        $path = $this->container->getParameter('claroline.param.files_directory')
            . DIRECTORY_SEPARATOR
            . $event->getResource()->getHashName();
        $[[file_mime]] = file_get_contents($path);
        $content = $this->container->get('templating')->render(
            '[[Vendor]][[Bundle]]Bundle::[[file_mime]].html.twig',
            array(
                'path' => $path,
                '[[file_mime]]' => $[[file_mime]],
                '_resource' => $event->getResource(),
            )
        );

        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
