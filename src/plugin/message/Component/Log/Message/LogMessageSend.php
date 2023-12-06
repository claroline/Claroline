<?php

namespace Claroline\MessageBundle\Component\Log\Message;

use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\LogBundle\Component\Log\AbstractMessageLog;

class LogMessageSend extends AbstractMessageLog
{
    public static function getName(): string
    {
        return 'message.send';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvents::MESSAGE_SENDING => ['logMessage', -25],
        ];
    }

    public function logMessage(SendMessageEvent $event): void
    {
        foreach ($event->getReceivers() as $receiver) {
            $this->log(
                $this->getTranslator()->trans('message.send', [
                    '%receiver%' => $receiver->getFullName(),
                ], 'log'),
                $receiver,
                $event->getSender()
            );
        }
    }
}
