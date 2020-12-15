<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Listener;

use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Claroline\MessageBundle\Manager\MessageManager;

class MessageListener
{
    /** @var MessageManager */
    private $messageManager;
    /** @var ScheduledTaskManager */
    private $taskManager;

    /**
     * MessageListener constructor.
     *
     * @param MessageManager       $messageManager
     * @param ScheduledTaskManager $taskManager
     */
    public function __construct(
        MessageManager $messageManager,
        ScheduledTaskManager $taskManager
    ) {
        $this->messageManager = $messageManager;
        $this->taskManager = $taskManager;
    }

    /**
     * @param SendMessageEvent $event
     */
    public function onMessageSending(SendMessageEvent $event)
    {
        $this->messageManager->sendMessageToAbstractRoleSubject(
            $event->getReceiver(),
            $event->getContent(),
            $event->getObject(),
            $event->getSender(),
            $event->getWithMail()
        );
    }

    /**
     * @param SendMessageEvent $event
     */
    public function onMessageSendingToUsers(SendMessageEvent $event)
    {
        $message = $this->messageManager->create(
            $event->getContent(),
            $event->getObject(),
            $event->getUsers(),
            $event->getSender()
        );

        $this->messageManager->send($message);
    }

    /**
     * @param GenericDataEvent $event
     */
    public function onExecuteMessageTask(GenericDataEvent $event)
    {
        /** @var ScheduledTask $task */
        $task = $event->getData();
        $data = $task->getData();
        $users = $task->getUsers();
        $object = isset($data['object']) ? $data['object'] : null;
        $content = isset($data['content']) ? $data['content'] : null;

        if (!empty($users) && !empty($object) && !empty($content)) {
            $message = $this->messageManager->create($content, $object, $users);
            $this->messageManager->send($message);
            $this->taskManager->markAsExecuted($task);
        }

        $event->stopPropagation();
    }
}
