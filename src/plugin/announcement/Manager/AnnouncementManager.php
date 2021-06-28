<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Manager;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Messenger\Message\SendAnnouncement;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Symfony\Component\Messenger\MessageBusInterface;

class AnnouncementManager
{
    /** @var ObjectManager */
    private $om;
    /** @var StrictDispatcher */
    private $eventDispatcher;
    /** @var MailManager */
    private $mailManager;
    /** @var ScheduledTaskManager */
    private $taskManager;
    /** @var FinderProvider */
    private $finder;
    /** @var MessageBusInterface */
    private $messageBus;

    public function __construct(
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        MailManager $mailManager,
        ScheduledTaskManager $taskManager,
        FinderProvider $finder,
        MessageBusInterface $messageBus
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->mailManager = $mailManager;
        $this->taskManager = $taskManager;
        $this->finder = $finder;
        $this->messageBus = $messageBus;
    }

    /**
     * Sends an Announcement by message to Users that can access it.
     */
    public function sendMessage(Announcement $announcement, array $users = [])
    {
        $message = $this->getMessage($announcement, $users);

        $this->messageBus->dispatch(new SendAnnouncement(
            $message['content'],
            $message['object'],
            $message['receivers'],
            $announcement->getId(),
            $message['sender']
        ));
    }

    public function scheduleMessage(Announcement $announcement, \DateTime $scheduledDate, array $roles = [])
    {
        $this->om->startFlushSuite();

        $message = $this->getMessage($announcement, $roles);
        $taskData = [
            'name' => $message['object'],
            'type' => 'message',
            'scheduledDate' => $scheduledDate->format('Y-m-d\TH:i:s'),
            'data' => [
                'object' => $message['object'],
                'content' => $announcement->getContent(),
                'users' => array_map(function (User $user) {
                    return ['id' => $user->getId()];
                }, $message['receivers']),
            ],
        ];

        if (empty($announcement->getTask())) {
            $task = $this->taskManager->create($taskData);

            // link new task to announcement
            $announcement->setTask($task);
            $this->om->persist($announcement);
        } else {
            $this->taskManager->update($taskData, $announcement->getTask());
        }

        $this->om->endFlushSuite();
    }

    public function unscheduleMessage(Announcement $announcement)
    {
        $this->om->startFlushSuite();

        if (!empty($announcement->getTask())) {
            $this->taskManager->delete($announcement->getTask());

            // unlink task and announcement
            $announcement->setTask(null);
            $this->om->persist($announcement);
        }

        $this->om->endFlushSuite();
    }

    /**
     * Gets the data which will be sent by message (internal &email) to Users.
     */
    private function getMessage(Announcement $announce, array $users = []): array
    {
        $resourceNode = $announce->getAggregate()->getResourceNode();

        $object = !empty($announce->getTitle()) ? $announce->getTitle() : $announce->getAggregate()->getName();

        if (empty($announce->getTitle()) && !empty($announce->getVisibleFrom())) {
            $object .= ' ['.$announce->getVisibleFrom()->format('Y-m-d H:i').']';
        }

        $content = $announce->getContent().'<br>['.$resourceNode->getWorkspace()->getCode().'] '.$resourceNode->getWorkspace()->getName();

        return [
            'sender' => $announce->getCreator(),
            'receivers' => $users,
            'object' => $object,
            'content' => $content,
        ];
    }
}
