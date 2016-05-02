<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 5/13/15
 */

namespace Icap\SocialmediaBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class LogListener.
 *
 * @DI\Service
 */
class LogListener extends ContainerAware
{
    /**
     * @DI\InjectParams({
     *      "container" = @DI\Inject("service_container")
     * })
     *
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * @param LogCreateDelegateViewEvent $event
     *
     * @DI\Observe("create_log_list_item_resource-icap_socialmedia-like_action")
     * @DI\Observe("create_log_list_item_resource-icap_socialmedia-share_action")
     * @DI\Observe("create_log_list_item_resource-icap_socialmedia-comment_action")
     */
    public function onCreateLogListItem(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'IcapSocialmediaBundle:Log:log_list_item.html.twig',
            array('log' => $event->getLog())
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @param LogCreateDelegateViewEvent $event
     *
     * @DI\Observe("create_log_details_resource-icap_socialmedia-like_action")
     * @DI\Observe("create_log_details_resource-icap_socialmedia-share_action")
     * @DI\Observe("create_log_details_resource-icap_socialmedia-comment_action")
     */
    public function onCreateLogDetails(LogCreateDelegateViewEvent $event)
    {
        $content = $this->container->get('templating')->render(
            'IcapSocialmediaBundle:Log:log_details.html.twig',
            array(
                'log' => $event->getLog(),
                'listItemView' => $this->container->get('templating')->render(
                    'IcapSocialmediaBundle:Log:log_list_item.html.twig',
                    array('log' => $event->getLog())
                ),
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
