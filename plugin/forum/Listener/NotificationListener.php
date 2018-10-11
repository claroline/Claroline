<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Event\Notification\NotificationUserParametersEvent;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class NotificationListener
{
    private $templating;

    /**
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating")
     * })
     */
    public function __construct($templating)
    {
        $this->templating = $templating;
    }

    /**
     * @param NotificationCreateDelegateViewEvent $event
     * @DI\Observe("create_notification_item_forum_message-create")
     */
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();
        $content = $this->templating->render(
            'ClarolineForumBundle:notification:notification_item.html.twig',
            [
                'notification' => $notification,
                'status' => $notificationView->getStatus(),
                'systemName' => $event->getSystemName(),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * @param NotificationUserParametersEvent $event
     *
     * @DI\Observe("icap_notification_user_parameters_event")
     */
    public function onGetTypesForParameters(NotificationUserParametersEvent $event)
    {
        $event->addTypes('forum');
    }
}
