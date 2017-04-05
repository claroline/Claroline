<?php

namespace UJM\ExoBundle\Listener\Log;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service()
 */
class DisplayLogListener
{
    use ContainerAwareTrait;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @DI\Observe("create_log_details_resource-ujm_exercise-exercise_evaluated")
     *
     * @param LogCreateDelegateViewEvent $event
     */
    public function onCreateLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'UJMExoBundle:Log:show.html.twig',
            [
                'log' => $event->getLog(),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
