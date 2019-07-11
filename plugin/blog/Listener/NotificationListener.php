<?php

namespace Icap\BlogBundle\Listener;

use Icap\BlogBundle\Event\Log\LogCommentCreateEvent;
use Icap\BlogBundle\Event\Log\LogCommentPublishEvent;
use Icap\BlogBundle\Event\Log\LogCommentUpdateEvent;
use Icap\BlogBundle\Event\Log\LogPostCreateEvent;
use Icap\BlogBundle\Event\Log\LogPostPublishEvent;
use Icap\BlogBundle\Event\Log\LogPostUpdateEvent;
use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NotificationListener.
 *
 * @DI\Service
 */
class NotificationListener
{
    private $templating;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator"),
     *     "router"     = @DI\Inject("router")
     * })
     */
    public function __construct(TranslatorInterface $translator, RouterInterface $router)
    {
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * @param NotificationCreateDelegateViewEvent $event
     * @DI\Observe("create_notification_item_resource-icap_blog-post_create")
     * @DI\Observe("create_notification_item_resource-icap_blog-post_update")
     * @DI\Observe("create_notification_item_resource-icap_blog-post_publish")
     * @DI\Observe("create_notification_item_resource-icap_blog-comment_create")
     * @DI\Observe("create_notification_item_resource-icap_blog-comment_update")
     * @DI\Observe("create_notification_item_resource-icap_blog-comment_publish")
     * @DI\Observe("create_notification_item_resource-icap_blog-post-user_tagged")
     * @DI\Observe("create_notification_item_resource-icap_blog-comment-user_tagged")
     */
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();

        if (LogPostCreateEvent::ACTION === $notification->getActionKey() || LogPostUpdateEvent::ACTION === $notification->getActionKey() || LogPostPublishEvent::ACTION === $notification->getActionKey()) {
            $event->setPrimaryAction([
              'url' => 'claro_resource_show_short',
              'parameters' => [
                'id' => $notification->getDetails()['resource']['uuid'],
                '#' => $notification->getDetails()['post']['details'],
              ],
            ]);
            $text = $this->translator->trans($notification->getActionKey(), ['%blog%' => $notification->getDetails()['resource']['name'], '%post%' => $notification->getDetails()['post']['title']], 'notification');
            $event->setText($text);
        } elseif (LogCommentCreateEvent::ACTION === $notification->getActionKey() || LogCommentUpdateEvent::ACTION === $notification->getActionKey() || LogCommentPublishEvent::ACTION === $notification->getActionKey()) {
            $event->setPrimaryAction([
            'url' => 'claro_resource_show_short',
              'parameters' => [
                'id' => $notification->getDetails()['resource']['uuid'],
                '#' => $notification->getDetails()['post']['details'],
              ],
            ]);
            $text = $this->translator->trans($notification->getActionKey(), ['%blog%' => $notification->getDetails()['resource']['name'], '%post%' => $notification->getDetails()['post']['title']], 'notification');
            $event->setText($text);
        } elseif ('resource-icap_blog-post-user_tagged' === $notification->getActionKey() || 'resource-icap_blog-comment-user_tagged' === $notification->getActionKey()) {
            $event->setPrimaryAction([
              'url' => 'claro_resource_show_short',
              'parameters' => [
                'id' => $notification->getDetails()['resource']['uuid'],
                '#' => $notification->getDetails()['post']['details'],
              ],
            ]);
            $text = $this->translator->trans($notification->getActionKey(), ['%blog%' => $notification->getDetails()['resource']['name'], '%post%' => $notification->getDetails()['post']['title']], 'notification');
            $event->setText($text);
        }
    }
}
