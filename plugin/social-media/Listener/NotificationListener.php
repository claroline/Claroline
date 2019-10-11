<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 5/18/15
 */

namespace Icap\SocialmediaBundle\Listener;

use Icap\NotificationBundle\Event\Notification\NotificationCreateDelegateViewEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class NotificationListener.
 */
class NotificationListener
{
    public function __construct(TranslatorInterface $translator, RouterInterface $router)
    {
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * @param NotificationCreateDelegateViewEvent $event
     */
    public function onCreateNotificationItem(NotificationCreateDelegateViewEvent $event)
    {
        $notificationView = $event->getNotificationView();
        $notification = $notificationView->getNotification();

        $primaryAction = [
          'url' => 'claro_resource_open_short',
          'parameters' => [
            'node' => $notification->getDetails()['resource']['id'],
          ],
        ];

        $text = '';

        switch ($notification->getActionKey()) {
          case LogSocialmediaLikeEvent::ACTION:
            $text .= $this->translator->trans('liked', [], 'icap_socialmedia');
            break;
          case LogSocialmediaShareEvent::ACTION:
            if (isset($notification->getDetails()['share']) && isset($notification->getDetails()['network'])) {
                $text .= $this->translator->trans('shared_on', ['%network%' => $notification->getDetails()['network']], 'icap_socialmedia');
            } else {
                $text .= $this->translator->trans('shared_on', ['%network%' => 'claroline'], 'icap_socialmedia');
            }
            break;
          case LogSocialmediaCommentEvent::ACTION:
            $text .= $this->translator->trans('commented', [], 'icap_socialmedia');
            $primaryAction = [
              'url' => 'icap_socialmedia_comments_view',
              'parameters' => [
                'resourceId' => $notification->getDetails()['resource']['id'],
              ],
            ];
            break;
        }

        $text .= ' '.$notification->getDetails()['resource']['name'];
        $event->setText($text);
        $event->setPrimaryAction($primaryAction);
    }
}
