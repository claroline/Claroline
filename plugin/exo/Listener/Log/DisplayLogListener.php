<?php

namespace UJM\ExoBundle\Listener\Log;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DisplayLogListener
{
    use ContainerAwareTrait;

    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    public function onCreateLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('twig')->render('@UJMExo/log/show.html.twig', [
            'log' => $event->getLog(),
        ]);

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
