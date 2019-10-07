<?php

namespace UJM\ExoBundle\Listener\Log;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DisplayLogListener
{
    use ContainerAwareTrait;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @param LogCreateDelegateViewEvent $event
     */
    public function onCreateLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'UJMExoBundle:log:show.html.twig',
            [
                'log' => $event->getLog(),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
