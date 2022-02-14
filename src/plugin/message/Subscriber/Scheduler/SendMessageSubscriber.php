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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\SchedulerBundle\Event\ExecuteScheduledTaskEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendMessageSubscriber implements EventSubscriberInterface
{
    /** @var StrictDispatcher */
    private $dispatcher;

    public function __construct(
        StrictDispatcher $dispatcher
    ) {
        $this->dispatcher = $dispatcher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'scheduler.execute.message' => 'executeTask',
        ];
    }

    public function executeTask(ExecuteScheduledTaskEvent $event)
    {
        $task = $event->getTask();

        $data = $task->getData();
        $users = $task->getUsers(); // TODO : to remove
        $object = isset($data['object']) ? $data['object'] : null;
        $content = isset($data['content']) ? $data['content'] : null;

        if (empty($users) || (empty($object) && empty($content))) {
            return;
        }

        $this->dispatcher->dispatch(MessageEvents::MESSAGE_SENDING, SendMessageEvent::class, [
            $content,
            $object,
            $users,
        ]);
    }
}
