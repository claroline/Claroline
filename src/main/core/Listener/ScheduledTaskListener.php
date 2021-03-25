<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;

class ScheduledTaskListener
{
    private $mailManager;
    private $taskManager;

    /**
     * ScheduledTaskListener constructor.
     */
    public function __construct(
        MailManager $mailManager,
        ScheduledTaskManager $taskManager
    ) {
        $this->mailManager = $mailManager;
        $this->taskManager = $taskManager;
    }

    public function onExecuteMailTask(GenericDataEvent $event)
    {
        /** @var ScheduledTask $task */
        $task = $event->getData();

        $data = $task->getData();
        $users = $task->getUsers();
        $object = isset($data['object']) ? $data['object'] : null;
        $content = isset($data['content']) ? $data['content'] : null;

        if (count($users) > 0 && !empty($object) && !empty($content)) {
            if ($this->mailManager->send($object, $content, $users)) {
                $this->taskManager->markAsExecuted($task);
            }
        }

        $event->stopPropagation();
    }
}
