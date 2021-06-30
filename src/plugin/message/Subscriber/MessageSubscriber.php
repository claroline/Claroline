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
use Claroline\MessageBundle\Messenger\Message\SendMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class MessageSubscriber implements EventSubscriberInterface
{
    private $messageManager;
    private $security;
    private $translator;
    private $messageBus;

    public function __construct(
        MessageManager $messageManager,
        Security $security,
        TranslatorInterface $translator,
        MessageBusInterface $messageBus
    ) {
        $this->messageManager = $messageManager;
        $this->security = $security;
        $this->translator = $translator;
        $this->messageBus = $messageBus;
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
            $event->getWithMail()
        );

        $sender = $event->getSender() ?? $this->security->getUser();

        foreach ($users as $user) {
            $this->messageBus->dispatch(new SendMessage(
                $event->getMessage($this->translator, $sender, $user),
                $eventName,
                $user->getId(),
                $sender->getId()
            ));
        }
    }
}
