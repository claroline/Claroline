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

use Claroline\AnnouncementBundle\API\Serializer\AnnouncementSerializer;
use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Entity\AnnouncementsWidgetConfig;
use Claroline\AnnouncementBundle\Repository\AnnouncementRepository;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\RoleRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.announcement_manager")
 */
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

    /** @var AnnouncementRepository */
    private $announcementRepo;
    private $announcementsWidgetConfigRepo;

    /** @var RoleRepository */
    private $roleRepo;

    /** @var UserRepository */
    private $userRepo;

    /**
     * AnnouncementManager constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "serializer"      = @DI\Inject("claroline.serializer.announcement"),
     *     "mailManager"     = @DI\Inject("claroline.manager.mail_manager"),
     *     "taskManager"     = @DI\Inject("claroline.manager.scheduled_task_manager")
     * })
     *
     * @param ObjectManager          $om
     * @param StrictDispatcher       $eventDispatcher
     * @param AnnouncementSerializer $serializer
     * @param MailManager            $mailManager
     * @param ScheduledTaskManager   $taskManager
     */
    public function __construct(
        ObjectManager $om,
        StrictDispatcher $eventDispatcher,
        AnnouncementSerializer $serializer,
        MailManager $mailManager,
        ScheduledTaskManager $taskManager
    ) {
        $this->om = $om;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
        $this->mailManager = $mailManager;
        $this->taskManager = $taskManager;

        $this->announcementRepo = $om->getRepository('ClarolineAnnouncementBundle:Announcement');
        $this->announcementsWidgetConfigRepo = $om->getRepository('ClarolineAnnouncementBundle:AnnouncementsWidgetConfig');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
    }

    /**
     * Serializes an Announcement entity.
     *
     * @param Announcement $announcement
     *
     * @return array
     */
    public function serialize(Announcement $announcement)
    {
        return $this->serializer->serialize($announcement);
    }

    /**
     * Creates a new Announcement.
     *
     * @param AnnouncementAggregate $aggregate
     * @param array                 $data
     *
     * @return Announcement
     */
    public function create(AnnouncementAggregate $aggregate, array $data)
    {
        $this->om->startFlushSuite();

        $announce = new Announcement();
        $announce->setAggregate($aggregate);

        $this->update($announce, $data, 'LogAnnouncementCreate');

        $this->om->endFlushSuite();

        return $announce;
    }

    /**
     * Updates an Announcement.
     *
     * @param Announcement $announcement
     * @param array        $data
     * @param string       $logEvent
     *
     * @return Announcement
     */
    public function update(Announcement $announcement, array $data, $logEvent = 'LogAnnouncementEdit')
    {
        $this->om->startFlushSuite();

        // deserialize data
        $this->serializer->deserialize($data, $announcement);
        $this->om->persist($announcement);

        // send message if needed
        if ($data['meta']['notifyUsers']) {
            // announcement should be sent to users
            if (!empty($announcement->getVisibleFrom())) {
                // schedule sending
                $this->scheduleMessage($announcement);
            } else {
                // directly send message
                $this->unscheduleMessage($announcement);
                $this->sendMessage($announcement);
            }
        } else {
            // announcement should not be sent
            $this->unscheduleMessage($announcement);
        }

        $this->om->endFlushSuite();

        // log
        $this->eventDispatcher->dispatch('log', 'Claroline\\AnnouncementBundle\\Event\\Log\\'.$logEvent.'Event', [$announcement->getAggregate(), $announcement]);

        return $announcement;
    }

    /**
     * Deletes an Announcement.
     *
     * @param Announcement $announcement
     */
    public function delete(Announcement $announcement)
    {
        $this->om->startFlushSuite();

        // delete scheduled task is any
        $this->unscheduleMessage($announcement);

        // log deletion
        $this->eventDispatcher->dispatch('log', 'Claroline\\AnnouncementBundle\\Event\\Log\\LogAnnouncementDeleteEvent', [$announcement->getAggregate(), $announcement]);

        // do remove
        $this->om->remove($announcement);
        $this->om->endFlushSuite();
    }

    public function getVisibleAnnouncementsByWorkspace(Workspace $workspace, array $roles)
    {
        if (in_array('ROLE_ADMIN', $roles)
            || in_array("ROLE_WS_MANAGER_{$workspace->getGuid()}", $roles)) {
            return $this->announcementRepo->findVisibleByWorkspace($workspace);
        }

        return $this->announcementRepo->findVisibleByWorkspaceAndRoles($workspace, $roles);
    }

    public function getVisibleAnnouncementsByWorkspaces(array $workspaces, array $roles)
    {
        $managerWorkspaces = [];
        $nonManagerWorkspaces = [];

        foreach ($workspaces as $workspace) {
            if (in_array("ROLE_WS_MANAGER_{$workspace->getGuid()}", $roles)) {
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
     * Sends an Announcement by mail to Users that can access it.
     *
     * @param Announcement $announcement
     */
    public function sendMail(Announcement $announcement)
    {
        $message = $this->getMessage($announcement);
        $this->mailManager->send(
            $message['object'],
            $message['content'],
            $message['receivers'],
            $message['sender']
        );
    }

    /**
     * Sends an Announcement by message to Users that can access it.
     *
     * @param Announcement $announcement
     */
    public function sendMessage(Announcement $announcement)
    {
        $message = $this->getMessage($announcement);

        $this->eventDispatcher->dispatch(
            'claroline_message_sending_to_users',
            'SendMessage',
            [
                $message['sender'],
                $message['content'],
                $message['object'],
                null,
                $message['receivers'],
            ]
        );
    }

    public function scheduleMessage(Announcement $announcement)
    {
        $this->om->startFlushSuite();

        $message = $this->getMessage($announcement);
        $taskData = [
            'name' => $message['object'],
            'type' => 'message',
            'scheduledDate' => $announcement->getVisibleFrom()->format('Y-m-d\TH:i:s'),
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
     *
     * @param Announcement $announce
     *
     * @return array
     */
    private function getMessage(Announcement $announce)
    {
        $resourceNode = $announce->getAggregate()->getResourceNode();

        $object = !empty($announce->getTitle()) ?
            $announce->getTitle() :
            $announce->getAggregate()->getName().' ['.$announce->getVisibleFrom()->format('Y-m-d H:i').']';

        $content = $announce->getContent().'<br>['.$resourceNode->getWorkspace()->getCode().'] '.$resourceNode->getWorkspace()->getName();

        return [
            'sender' => $announce->getCreator(),
            'receivers' => $this->getVisibleBy($announce),
            'object' => $object,
            'content' => $content,
        ];
    }

    /**
     * Gets the list of Users that can see an announce.
     *
     * @todo make a dql request to retrieve the users (it may be a difficult one to do)
     *
     * @param Announcement $announce
     *
     * @return User[]
     */
    private function getVisibleBy(Announcement $announce)
    {
        $node = $announce->getAggregate()->getResourceNode();

        $rights = $node->getRights();
        $roles = [];
        foreach ($rights as $right) {
            //1 is the default "open" mask
            if ($right->getMask() & 1) {
                $roles[] = $right->getRole();
            }
        }

        $roles[] = $this->roleRepo->findOneBy([
            'name' => 'ROLE_WS_MANAGER_'.$node->getWorkspace()->getGuid(),
        ]);

        return $this->userRepo->findByRolesIncludingGroups($roles, false, 'id', 'ASC');
    }

    public function getAnnouncementsWidgetConfig(WidgetInstance $widgetInstance)
    {
        $config = $this->announcementsWidgetConfigRepo->findOneBy(['widgetInstance' => $widgetInstance]);

        if (is_null($config)) {
            $config = new AnnouncementsWidgetConfig();
            $config->setWidgetInstance($widgetInstance);
            $this->om->persist($config);
            $this->om->flush();
        }

        return $config;
    }

    public function persistAnnouncementsWidgetConfig(AnnouncementsWidgetConfig $config)
    {
        $this->om->persist($config);
        $this->om->flush();
    }
}
