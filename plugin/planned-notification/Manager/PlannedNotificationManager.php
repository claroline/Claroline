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
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Claroline\CoreBundle\Repository\Log\LogRepository;
use Claroline\PlannedNotificationBundle\Entity\Message;
use Claroline\PlannedNotificationBundle\Entity\PlannedNotification;
use Claroline\PlannedNotificationBundle\Repository\PlannedNotificationRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

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

    /** @var TranslatorInterface */
    private $translator;

    /** @var LogRepository */
    private $logRepo;

    /** @var PlannedNotificationRepository */
    private $plannedNotificationRepo;

    /**
     * PlannedNotificationManager constructor.
     *
     * @DI\InjectParams({
     *     "mailManager"          = @DI\Inject("claroline.manager.mail_manager"),
     *     "om"                   = @DI\Inject("claroline.persistence.object_manager"),
     *     "scheduledTaskManager" = @DI\Inject("claroline.manager.scheduled_task_manager"),
     *     "translator"           = @DI\Inject("translator")
     * })
     *
     * @param MailManager          $mailManager
     * @param ObjectManager        $om
     * @param ScheduledTaskManager $scheduledTaskManager
     * @param TranslatorInterface  $translator
     */
    public function __construct(
        MailManager $mailManager,
        ObjectManager $om,
        ScheduledTaskManager $scheduledTaskManager,
        TranslatorInterface $translator
    ) {
        $this->mailManager = $mailManager;
        $this->om = $om;
        $this->scheduledTaskManager = $scheduledTaskManager;
        $this->translator = $translator;

        $this->logRepo = $om->getRepository('Claroline\CoreBundle\Entity\Log\Log');
        $this->plannedNotificationRepo = $om->getRepository('Claroline\PlannedNotificationBundle\Entity\PlannedNotification');
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
            $name = $this->translator->trans($notification->getAction(), [], 'planned_notification');

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
}
