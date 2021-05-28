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
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Messenger\Message\SendMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendMessageHandler implements MessageHandlerInterface
{
    /** @var StrictDispatcher */
    private $dispatcher;

    public function __construct(StrictDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function __invoke(SendMessage $message)
    {
        $this->dispatcher->dispatch(MessageEvents::MESSAGE_SENDING, SendMessageEvent::class, [
            $message->getContent(),
            $message->getObject(),
            $message->getReceivers(),
        ]);
    }
}
