<?php

namespace Claroline\MessageBundle\Component\Notification;

use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\NotificationBundle\Component\Notification\AbstractNotification;

/**
 * Notify user when they receive a new message.
 */
class NewMessageNotification extends AbstractNotification
{
    public static function getName(): string
    {
        return 'message';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvents::MESSAGE_SENDING => 'notifyNewMessage',
        ];
    }

    public function notifyNewMessage(SendMessageEvent $event): void
    {
        $this->notify('Vous avez reçu nouveau message', $event->getReceivers());
        /*foreach ($event->getReceivers() as $receiver) {
            // $event->getSender()

        }*/
    }
}
