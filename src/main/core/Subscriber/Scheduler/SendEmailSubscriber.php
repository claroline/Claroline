<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Subscriber\Scheduler;

use Claroline\CoreBundle\Manager\MailManager;
use Claroline\SchedulerBundle\Event\ExecuteScheduledTaskEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendEmailSubscriber implements EventSubscriberInterface
{
    /** @var MailManager */
    private $mailManager;

    public function __construct(
        MailManager $mailManager
    ) {
        $this->mailManager = $mailManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'scheduler.execute.email' => 'executeTask',
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

        $this->mailManager->send($object, $content, $users);
    }
}
