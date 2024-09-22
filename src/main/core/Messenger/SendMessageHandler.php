<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Messenger\Message\SendMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendMessageHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om
    ) {
    }

    public function __invoke(SendMessage $message): void
    {
        $receivers = [];
        foreach ($message->getReceiverIds() as $receiverId) {
            $receiver = $this->om->getRepository(User::class)->find($receiverId);
            if (!empty($receiver)) {
                $receivers[] = $receiver;
            }
        }

        $sender = null;
        if ($message->getSenderId()) {
            $sender = $this->om->getRepository(User::class)->find($message->getSenderId());
        }

        $event = new SendMessageEvent($message->getContent(), $message->getObject(), $receivers, $sender);
        $this->eventDispatcher->dispatch($event, MessageEvents::MESSAGE_SENDING);
    }
}
