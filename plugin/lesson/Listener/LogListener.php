<?php

namespace Icap\LessonBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAware;

class LogListener extends ContainerAware
{
    public function onCreateLogListItem(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'IcapLessonBundle:Log:log_list_item.html.twig',
            array('log' => $event->getLog())
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onChapterCreateLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'IcapLessonBundle:Log:log_details.html.twig',
            array(
                'log' => $event->getLog(),
                'listItemView' => $this->container->get('templating')->render(
                    'IcapLessonBundle:Log:log_list_item.html.twig',
                    array('log' => $event->getLog())
                ),
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
