<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogRoleSubscribeEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\PlannedNotificationBundle\Manager\PlannedNotificationManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogListener
{
    /** @var PlannedNotificationManager */
    private $manager;
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param PlannedNotificationManager $manager
     * @param TokenStorageInterface      $tokenStorage
     */
    public function __construct(
        PlannedNotificationManager $manager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->manager = $manager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param LogGenericEvent $event
     */
    public function onLog(LogGenericEvent $event)
    {
        if ($event instanceof LogRoleSubscribeEvent) {
            $role = $event->getRole();
            $workspace = $role->getWorkspace();

            if (!empty($workspace)) {
                $this->manager->generateScheduledTasks(
                    $event->getActionKey(),
                    $event->getReceiver(),
                    $workspace,
                    $event->getReceiverGroup(),
                    $role
                );
            }
        } elseif ($event instanceof LogWorkspaceEnterEvent) {
            $user = $this->tokenStorage->getToken()->getUser();

            if ('anon.' !== $user) {
                $this->manager->generateScheduledTasks($event->getAction(), $user, $event->getWorkspace());
            }
        }
    }
}
