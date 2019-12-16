<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;

class WorkspaceUserQueueManager
{
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var RoleManager */
    private $roleManager;

    /**
     * WorkspaceUserQueueManager constructor.
     *
     * @param StrictDispatcher $dispatcher
     * @param ObjectManager    $om
     * @param RoleManager      $roleManager
     */
    public function __construct(
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        RoleManager $roleManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->om = $om;
        $this->roleManager = $roleManager;
    }

    /**
     * Validates a pending workspace registration.
     *
     * @param WorkspaceRegistrationQueue $workspaceRegistration
     */
    public function validateRegistration(WorkspaceRegistrationQueue $workspaceRegistration)
    {
        $this->roleManager->associateRole($workspaceRegistration->getUser(), $workspaceRegistration->getRole());

        $this->om->remove($workspaceRegistration);
        $this->om->flush();
    }

    /**
     * Removes a pending workspace registration.
     *
     * @param WorkspaceRegistrationQueue $workspaceRegistration
     */
    public function removeRegistration(WorkspaceRegistrationQueue $workspaceRegistration)
    {
        $this->dispatcher->dispatch(
            'log',
            'Log\LogWorkspaceRegistrationDecline',
            [$workspaceRegistration]
        );

        $this->om->remove($workspaceRegistration);
        $this->om->flush();
    }
}
