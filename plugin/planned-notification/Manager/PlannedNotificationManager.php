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
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Claroline\PlannedNotificationBundle\Repository\PlannedNotificationRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("claroline.manager.planned_notification_manager")
 */
class PlannedNotificationManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ScheduledTaskManager */
    private $scheduledTaskManager;

    /** @var TranslatorInterface */
    private $translator;

    /** @var PlannedNotificationRepository */
    private $plannedNotificationRepo;

    /**
     * PlannedNotificationManager constructor.
     *
     * @DI\InjectParams({
     *     "om"                   = @DI\Inject("claroline.persistence.object_manager"),
     *     "scheduledTaskManager" = @DI\Inject("claroline.manager.scheduled_task_manager"),
     *     "translator"           = @DI\Inject("translator")
     * })
     *
     * @param ObjectManager        $om
     * @param ScheduledTaskManager $scheduledTaskManager
     * @param TranslatorInterface  $translator
     */
    public function __construct(
        ObjectManager $om,
        ScheduledTaskManager $scheduledTaskManager,
        TranslatorInterface $translator
    ) {
        $this->om = $om;
        $this->scheduledTaskManager = $scheduledTaskManager;
        $this->translator = $translator;

        $this->plannedNotificationRepo = $om->getRepository('Claroline\PlannedNotificationBundle\Entity\PlannedNotification');
    }

    /**
     * @param Workspace $workspace
     * @param string    $action
     * @param User      $user
     * @param Group     $group
     * @param Role      $role
     */
    public function generateScheduledTasks(Workspace $workspace, $action, User $user = null, Group $group = null, Role $role = null)
    {
        $notifications = is_null($role) ?
            $this->plannedNotificationRepo->findByAction($workspace, $action) :
            $this->plannedNotificationRepo->findByActionAndRole($workspace, $action, $role);

        $this->om->startFlushSuite();
        $currentDate = new \DateTime();

        foreach ($notifications as $notification) {
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
}
