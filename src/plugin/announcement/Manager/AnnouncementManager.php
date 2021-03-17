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
use Claroline\AnnouncementBundle\Entity\AnnouncementSend;
use Claroline\AnnouncementBundle\Repository\AnnouncementRepository;
use Claroline\AnnouncementBundle\Serializer\AnnouncementSerializer;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Claroline\CoreBundle\Repository\User\RoleRepository;
use Claroline\CoreBundle\Repository\User\UserRepository;

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
    /** @var AnnouncementSerializer */
    private $serializer;
    /** @var FinderProvider */
    private $finder;
    /** @var AnnouncementRepository */
    private $announcementRepo;
    /** @var RoleRepository */
    private $roleRepo;
    /** @var UserRepository */
    private $userRepo;

    public function __construct(
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        AnnouncementSerializer $serializer,
        MailManager $mailManager,
        ScheduledTaskManager $taskManager,
        FinderProvider $finder
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
        $this->mailManager = $mailManager;
        $this->taskManager = $taskManager;
        $this->finder = $finder;

        $this->announcementRepo = $om->getRepository('ClarolineAnnouncementBundle:Announcement');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
    }

    /**
     * Serializes an Announcement entity.
     *
     * @return array
     */
    public function serialize(Announcement $announcement)
    {
        return $this->serializer->serialize($announcement);
    }

    public function getVisibleAnnouncementsByWorkspace(Workspace $workspace, array $roles)
    {
        if (in_array('ROLE_ADMIN', $roles)
            || in_array("ROLE_WS_MANAGER_{$workspace->getUuid()}", $roles)) {
            return $this->announcementRepo->findVisibleByWorkspace($workspace);
        }

        return $this->announcementRepo->findVisibleByWorkspaceAndRoles($workspace, $roles);
    }

    public function getVisibleAnnouncementsByWorkspaces(array $workspaces, array $roles)
    {
        $managerWorkspaces = [];
        $nonManagerWorkspaces = [];

        foreach ($workspaces as $workspace) {
            if (in_array("ROLE_WS_MANAGER_{$workspace->getUuid()}", $roles)) {
                $managerWorkspaces[] = $workspace;
            } else {
                $nonManagerWorkspaces[] = $workspace;
            }
        }

        return $this->announcementRepo->findVisibleByWorkspacesAndRoles(
            $nonManagerWorkspaces,
            $managerWorkspaces,
            $roles
        );
    }

    /**
     * Sends an Announcement by message to Users that can access it.
     */
    public function sendMessage(Announcement $announcement, array $users = [])
    {
        $message = $this->getMessage($announcement, $users);

        $announcementSend = new AnnouncementSend();

        $data = $message;
        $data['receivers'] = array_map(function (User $receiver) {
            return $receiver->getUsername();
        }, $message['receivers']);
        $data['sender'] = $message['sender']->getUsername();
        $announcementSend->setAnnouncement($announcement);
        $announcementSend->setData($data);
        $this->om->persist($announcementSend);
        $this->om->flush();

        $this->eventDispatcher->dispatch(
            MessageEvents::MESSAGE_SENDING,
            SendMessageEvent::class,
            [
                $message['content'],
                $message['object'],
                null,
                $message['receivers'],
                $message['sender'],
            ]
        );

        //it's kind of a hack because this is not using the crud... but wathever.
        $this->eventDispatcher->dispatch('crud.post.create.announcement_send', 'Claroline\\AppBundle\\Event\\Crud\\CreateEvent', [
          $announcementSend, [], [],
        ]);
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
