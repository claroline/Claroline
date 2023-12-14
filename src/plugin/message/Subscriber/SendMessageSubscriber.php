<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Subscriber;

use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendMessageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageManager $messageManager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvents::MESSAGE_SENDING => 'onMessageSending',
        ];
    }

    public function onMessageSending(SendMessageEvent $event): void
    {
        $this->messageManager->sendMessage(
            $event->getContent(),
            $event->getObject(),
            $event->getReceivers(),
            $event->getSender(),
            $event->getAttachments()
        );
    }
}
