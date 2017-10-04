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
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Entity\AnnouncementsWidgetConfig;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.announcement.manager.announcement_manager")
 */
class AnnouncementManager
{
    private $eventDispatcher;
    private $mailManager;
    private $om;
    private $configHandler;
    private $taskManager;

    private $announcementRepo;
    private $announcementsWidgetConfigRepo;
    private $roleRepo;
    private $userRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "mailManager"     = @DI\Inject("claroline.manager.mail_manager"),
     *     "eventDispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "configHandler"   = @DI\Inject("claroline.config.platform_config_handler"),
     *     "taskManager"     = @DI\Inject("claroline.manager.scheduled_task_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        MailManager $mailManager,
        StrictDispatcher $eventDispatcher,
        PlatformConfigurationHandler $configHandler,
        ScheduledTaskManager $taskManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->mailManager = $mailManager;
        $this->om = $om;
        $this->configHandler = $configHandler;
        $this->taskManager = $taskManager;
        $this->announcementRepo = $om->getRepository('ClarolineAnnouncementBundle:Announcement');
        $this->announcementsWidgetConfigRepo = $om->getRepository('ClarolineAnnouncementBundle:AnnouncementsWidgetConfig');
        $this->roleRepo = $om->getRepository('ClarolineCoreBundle:Role');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
    }

    public function insertAnnouncement(Announcement $announcement)
    {
        $this->om->persist($announcement);
        $this->om->flush();
    }

    public function deleteAnnouncement(Announcement $announcement)
    {
        $this->om->startFlushSuite();
        $task = $announcement->getTask();

        if (!is_null($task)) {
            $this->deleteAnnouncementTask($announcement);
        }
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

    public function getAllAnnouncementsByAggregate(AnnouncementAggregate $aggregate)
    {
        return $this->announcementRepo->findAllAnnouncementsByAggregate($aggregate);
    }

    public function getVisibleAnnouncementsByAggregate(AnnouncementAggregate $aggregate)
    {
        return $this->announcementRepo->findVisibleAnnouncementsByAggregate($aggregate);
    }

    public function sendMessage(Announcement $announcement)
    {
        $targets = $this->getUsersByResource($announcement->getAggregate()->getResourceNode(), 1);
        $workspace = $announcement->getAggregate()->getResourceNode()->getWorkspace();
        $content = $announcement->getContent().'<br>['.$workspace->getCode().'] '.$workspace->getName();
        $this->eventDispatcher->dispatch(
            'claroline_message_sending_to_users',
            'SendMessage',
            [
                $announcement->getCreator(),
                $content,
                $announcement->getTitle(),
                null,
                $targets,
            ]
        );
    }

    public function sendMail(Announcement $announcement, $users = null)
    {
        $targets = is_null($users) ? $this->getUsersByResource($announcement->getAggregate()->getResourceNode(), 1) : $users;
        $workspace = $announcement->getAggregate()->getResourceNode()->getWorkspace();
        $title = '['.$workspace->getCode().'] '.$announcement->getTitle();
        $content = $announcement->getContent().'<br>['.$workspace->getCode().'] '.$workspace->getName();
        $this->mailManager->send($title, $content, $targets, $announcement->getCreator());
    }

    //@todo make a dql request to retrieve the users (it may be a difficult one to do)
    public function getUsersByResource(ResourceNode $node, $mask)
    {
        $rights = $node->getRights();
        $roles = [];

        foreach ($rights as $right) {
            //1 is the default "open" mask
            if ($right->getMask() & 1) {
                $roles[] = $right->getRole();
            }
        }

        $roles[] = $this->roleRepo->findOneByName('ROLE_WS_MANAGER_'.$node->getWorkspace()->getGuid());
        //we must also add the ROLE_WS_MANAGER_{ws_guid}

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

    public function createAnnouncementTask(Announcement $announcement)
    {
        if (!is_null($announcement->getVisibleFrom()) &&
            $this->configHandler->hasParameter('is_cron_configured') &&
            $this->configHandler->getParameter('is_cron_configured')
        ) {
            $this->om->startFlushSuite();
            $taskData = $this->generateAnnoucementTaskData($announcement);
            $task = $this->taskManager->create($taskData);
            $announcement->setTask($task);
            $this->om->persist($announcement);
            $this->om->endFlushSuite();
        }
    }

    public function updateAnnouncementTask(Announcement $announcement)
    {
        $task = $announcement->getTask();
        $visibleFromDate = $announcement->getVisibleFrom();

        if (!is_null($task)) {
            if (!is_null($visibleFromDate) &&
                $this->configHandler->hasParameter('is_cron_configured') &&
                $this->configHandler->getParameter('is_cron_configured')
            ) {
                $taskData = $this->generateAnnoucementTaskData($announcement);
                $this->taskManager->update($taskData, $task);
            } else {
                $this->deleteAnnouncementTask($announcement);
            }
        } elseif (!is_null($visibleFromDate)) {
            $this->createAnnouncementTask($announcement);
        }
    }

    public function deleteAnnouncementTask(Announcement $announcement)
    {
        $task = $announcement->getTask();

        if (!is_null($task)) {
            $this->om->startFlushSuite();
            $this->taskManager->delete($task);
            $announcement->setTask(null);
            $this->om->persist($announcement);
            $this->om->endFlushSuite();
        }
    }

    private function generateAnnoucementTaskData(Announcement $announcement)
    {
        $name = $announcement->getTitle() ?
            $announcement->getTitle() :
            $announcement->getAggregate()->getResourceNode()->getName().' ['.date('Y-m-d H:i').']';
        $object = $announcement->getTitle() ?
            $announcement->getTitle() :
            $announcement->getAggregate()->getResourceNode()->getName().' ['.$announcement->getVisibleFrom()->format('Y-m-d H:i').']';
        $users = [];
        $targets = $this->getUsersByResource($announcement->getAggregate()->getResourceNode(), 1);

        foreach ($targets as $target) {
            $users[] = ['id' => $target->getId()];
        }

        return [
            'name' => $name,
            'type' => 'message',
            'scheduledDate' => $announcement->getVisibleFrom()->format('Y-m-d\TH:i:s'),
            'data' => [
                'object' => $object,
                'content' => $announcement->getContent(),
                'users' => $users,
            ],
        ];
    }
}
