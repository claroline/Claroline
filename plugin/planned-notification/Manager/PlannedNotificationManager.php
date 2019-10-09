<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Claroline\CoreBundle\Repository\Log\LogRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\PlannedNotificationBundle\Entity\Message;
use Claroline\PlannedNotificationBundle\Entity\PlannedNotification;
use Claroline\PlannedNotificationBundle\Repository\PlannedNotificationRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.planned_notification_manager")
 */
class PlannedNotificationManager
{
    /** @var MailManager */
    private $mailManager;

    /** @var ObjectManager */
    private $om;

    /** @var ScheduledTaskManager */
    private $scheduledTaskManager;

    /** @var LogRepository */
    private $logRepo;

    /** @var PlannedNotificationRepository */
    private $plannedNotificationRepo;

    /** @var UserRepository */
    private $userRepo;

    /**
     * PlannedNotificationManager constructor.
     *
     * @param MailManager          $mailManager
     * @param ObjectManager        $om
     * @param ScheduledTaskManager $scheduledTaskManager
     */
    public function __construct(
        MailManager $mailManager,
        ObjectManager $om,
        ScheduledTaskManager $scheduledTaskManager
    ) {
        $this->mailManager = $mailManager;
        $this->om = $om;
        $this->scheduledTaskManager = $scheduledTaskManager;

        $this->logRepo = $om->getRepository(Log::class);
        $this->plannedNotificationRepo = $om->getRepository(PlannedNotification::class);
        $this->userRepo = $om->getRepository(User::class);
    }

    /**
     * @param string    $action
     * @param User      $user
     * @param Workspace $workspace
     * @param Group     $group
     * @param Role      $role
     */
    public function generateScheduledTasks(
        $action,
        User $user = null,
        Workspace $workspace = null,
        Group $group = null,
        Role $role = null
    ) {
        $notifications = [];
        $currentDate = new \DateTime();
        $isFirstConnection = null;

        switch ($action) {
            case PlannedNotification::TYPE_WORKSPACE_USER_REGISTRATION:
            case PlannedNotification::TYPE_WORKSPACE_GROUP_REGISTRATION:
                $notifications = is_null($role) ?
                    $this->plannedNotificationRepo->findByAction($workspace, $action) :
                    $this->plannedNotificationRepo->findByActionAndRole($workspace, $action, $role);
                break;
            case PlannedNotification::TYPE_WORKSPACE_FIRST_CONNECTION:
                $notifications = $this->plannedNotificationRepo->findByAction($workspace, $action);
                break;
        }

        $this->om->startFlushSuite();

        foreach ($notifications as $notification) {
            if (PlannedNotification::TYPE_WORKSPACE_FIRST_CONNECTION === $action) {
                if (is_null($isFirstConnection)) {
                    $logs = $this->logRepo->findBy(['action' => $action, 'doer' => $user, 'workspace' => $workspace]);
                    $isFirstConnection = 0 === count($logs);
                }
                if (!$isFirstConnection) {
                    continue;
                }
            }
            $name = $notification->getMessage()->getTitle();

            if (!empty($user)) {
                $name .= ' ('.$user->getUsername().')';
            }
            if (!empty($group)) {
                $name .= ' ('.$group->getName().')';
            }
            $name .= ' [+'.$notification->getInterval().']';
            $scheduledDate = clone $currentDate;
            $scheduledDate->add(new \DateInterval('P'.$notification->getInterval().'D'));
            $users = [];

            if (!empty($user)) {
                $users[$user->getId()] = $user;
            }
            if (!empty($group)) {
                foreach ($group->getUsers() as $groupUser) {
                    $users[$groupUser->getId()] = $groupUser;
                }
            }

            $data = [
                'name' => $name,
                'scheduledDate' => $scheduledDate->format('Y-m-d\TH:i:s'),
                'workspace' => [
                    'id' => $workspace->getId(),
                ],
                'data' => [
                    'object' => $notification->getMessage()->getTitle(),
                    'content' => $notification->getMessage()->getContent(),
                ],
                'users' => array_map(function (User $u) {
                    return ['id' => $u->getId()];
                }, $users),
            ];

            if ($notification->isByMail()) {
                $data['type'] = 'email';
                $this->scheduledTaskManager->create($data);
            }
            if ($notification->isByMessage()) {
                $data['type'] = 'message';
                $this->scheduledTaskManager->create($data);
            }
        }
        $this->om->endFlushSuite();
    }

    /**
     * @param Message[] $messages
     * @param User[]    $users
     */
    public function sendMessages(array $messages, array $users)
    {
        foreach ($messages as $message) {
            $this->mailManager->send($message->getTitle(), $message->getContent(), $users);
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function generateManualScheduledTasks(array $data)
    {
        $tasks = [];
        $date = isset($data['date']) ? new \DateTime($data['date']) : new \DateTime();
        $notificationsData = isset($data['notifications']) ? $data['notifications'] : [];
        $usersData = isset($data['users']) ? $data['users'] : [];

        $this->om->startFlushSuite();

        foreach ($notificationsData as $notification) {
            foreach ($usersData as $user) {
                $name = $notification['message']['title'];
                $name .= ' ('.$user['username'].')';
                $name .= ' [+'.$notification['parameters']['interval'].']';
                $scheduledDate = clone $date;
                $scheduledDate->add(new \DateInterval('P'.$notification['parameters']['interval'].'D'));

                $data = [
                    'name' => $name,
                    'scheduledDate' => $scheduledDate->format('Y-m-d\TH:i:s'),
                    'workspace' => [
                        'id' => $notification['workspace']['id'],
                    ],
                    'data' => [
                        'object' => $notification['message']['title'],
                        'content' => $notification['message']['content'],
                    ],
                    'users' => [
                        ['id' => $user['autoId']],
                    ],
                ];

                if ($notification['parameters']['byMail']) {
                    $data['type'] = 'email';
                    $task = $this->scheduledTaskManager->create($data);
                    $tasks[] = $task;
                }
                if ($notification['parameters']['byMessage']) {
                    $data['type'] = 'message';
                    $task = $this->scheduledTaskManager->create($data);
                    $tasks[] = $task;
                }
            }
        }
        $this->om->endFlushSuite();

        return $tasks;
    }
}
