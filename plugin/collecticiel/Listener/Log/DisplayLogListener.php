<?php

namespace Innova\CollecticielBundle\Listener\Log;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DisplayLogListener
{
    use ContainerAwareTrait;

    public function onCreateLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'InnovaCollecticielBundle:Log:log_details.html.twig',
            [
                'log' => $event->getLog(),
                'listItemView' => $this->container->get('templating')->render(
                    'InnovaCollecticielBundle:Log:log_list_item.html.twig',
                    ['log' => $event->getLog()]
                ),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
