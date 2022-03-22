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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Messenger\Message\SendMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendMessageHandler implements MessageHandlerInterface
{
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        StrictDispatcher $dispatcher,
        ObjectManager $om
    ) {
        $this->dispatcher = $dispatcher;
        $this->om = $om;
    }

    public function __invoke(SendMessage $message)
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

        $this->dispatcher->dispatch(MessageEvents::MESSAGE_SENDING, SendMessageEvent::class, [
            $message->getContent(),
            $message->getObject(),
            $receivers,
            $sender,
        ]);
    }
}
