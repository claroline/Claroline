<?php

namespace UJM\ExoBundle\Listener\Log;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @DI\Service()
 */
class DisplayLogListener
{
    use ContainerAwareTrait;

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
