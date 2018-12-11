<?php

namespace Icap\WikiBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * NotificationListener.
 *
 * @DI\Service
 */
class NotificationListener
{
    /** @var TwigEngine */
    private $templating;

    /**
     * NotificationListener constructor.
     *
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     *
     * @param TwigEngine $templating
     */
    public function __construct(TwigEngine $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @DI\Observe("create_notification_item_resource-icap_wiki-section_create")
     * @DI\Observe("create_notification_item_resource-icap_wiki-contribution_create")
     * @DI\Observe("create_notification_item_resource-icap_wiki-user_tagged")
     *
     * @param NotificationCreateDelegateViewEvent $event
     */
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->templating->render(
            'IcapWikiBundle:notification:notification_item.html.twig',
            [
                'notification' => $notification,
                'status' => $notificationView->getStatus(),
                'systemName' => $event->getSystemName(),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
}
