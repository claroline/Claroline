<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Workspace;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceRegistrationQueue;

class WorkspaceUserQueueManager
{
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;

    public function __construct(
        StrictDispatcher $dispatcher,
        ObjectManager $om,
        Crud $crud
    ) {
        $this->dispatcher = $dispatcher;
        $this->om = $om;
        $this->crud = $crud;
    }

    /**
     * Validates a pending workspace registration.
     */
    public function validateRegistration(WorkspaceRegistrationQueue $workspaceRegistration)
    {
        if (!$workspaceRegistration->getUser()->hasRole($workspaceRegistration->getRole())) {
            $this->crud->patch($workspaceRegistration->getUser(), 'role', Crud::COLLECTION_ADD, [
                $workspaceRegistration->getRole(),
            ]);
        }

        $this->om->remove($workspaceRegistration);
        $this->om->flush();
    }

    /**
     * Removes a pending workspace registration.
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
