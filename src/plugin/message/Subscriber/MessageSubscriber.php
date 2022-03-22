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
use Claroline\LogBundle\Entity\MessageLog;
use Claroline\MessageBundle\Manager\MessageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageSubscriber implements EventSubscriberInterface
{
    private $messageManager;
    private $em;
    private $translator;

    public function __construct(
        MessageManager $messageManager,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ) {
        $this->messageManager = $messageManager;
        $this->em = $em;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvents::MESSAGE_SENDING => 'onMessageSending',
        ];
    }

    public function onMessageSending(SendMessageEvent $event, string $eventName)
    {
        $users = $this->messageManager->sendMessage(
            $event->getContent(),
            $event->getObject(),
            $event->getReceivers(),
            $event->getSender(),
            $event->getAttachments()
        );

        foreach ($users as $user) {
            $logEntry = new MessageLog();
            $logEntry->setDetails($event->getMessage($this->translator, $event->getSender(), $user));
            $logEntry->setEvent($eventName);
            $logEntry->setReceiver($user);
            $logEntry->setSender($event->getSender());

            $this->em->persist($logEntry);
        }

        $this->em->flush();
    }
}
