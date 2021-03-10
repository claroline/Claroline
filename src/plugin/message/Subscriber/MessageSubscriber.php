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
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;

class MessageSubscriber implements EventSubscriberInterface
{
    private $messageManager;

    public function __construct(MessageManager $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvents::MESSAGE_SENDING => 'onMessageSending',
        ];
    }

    public function onMessageSending(Event $event, string $eventName)
    {
        $this->messageManager->sendMessage(
            $event->getContent(),
            $event->getObject(),
            $event->getReceiver(),
            $event->getUsers(),
            $event->getSender(),
            $event->getWithMail()
        );
    }
}
