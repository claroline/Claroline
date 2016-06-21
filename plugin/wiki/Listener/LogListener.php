<?php

namespace Icap\WikiBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use Symfony\Component\DependencyInjection\ContainerAware;

class LogListener extends ContainerAware
{
    public function onCreateLogListItem(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'IcapWikiBundle:Log:log_list_item.html.twig',
            array('log' => $event->getLog())
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onSectionCreateLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'IcapWikiBundle:Log:log_details.html.twig',
            array(
                'log' => $event->getLog(),
                'listItemView' => $this->container->get('templating')->render(
                    'IcapWikiBundle:Log:log_list_item.html.twig',
                    array('log' => $event->getLog())
                ),
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
