<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Subscriber\Scheduler;

use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\SchedulerBundle\Event\ExecuteScheduledTaskEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendMessageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'scheduler.execute.message' => 'executeTask',
        ];
    }

    public function executeTask(ExecuteScheduledTaskEvent $event): void
    {
        $task = $event->getTask();

        $data = $task->getData();
        $users = $task->getUsers(); // TODO : to remove
        $object = isset($data['object']) ? $data['object'] : null;
        $content = isset($data['content']) ? $data['content'] : null;

        if (empty($users) || (empty($object) && empty($content))) {
            return;
        }

        $sendEvent = new SendMessageEvent($content, $object, $users);
        $this->dispatcher->dispatch($sendEvent, MessageEvents::MESSAGE_SENDING);
    }
}
