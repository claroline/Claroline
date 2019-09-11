<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\DBAL\Driver\Connection;

class RoleCrud
{
    /**
     * @param StrictDispatcher $dispatcher
     */
    public function __construct(
        StrictDispatcher $dispatcher,
        Connection $conn
    ) {
        //too many dependencies, simplify this when we can
        $this->dispatcher = $dispatcher;
        $this->conn = $conn;
    }

    /**
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        /** @var Role $role */
        $role = $event->getObject();

        if (!$role->getWorkspace()) {
            $role->setName(strtoupper('role_'.$role->getTranslationKey()));
        }
    }

    /**
     * @param CreateEvent $event
     */
    public function postCreate(CreateEvent $event)
    {
        /** @var Role $role */
        $role = $event->getObject();

        if ($role->getWorkspace()) {
            $sql = "
              INSERT INTO claro_resource_rights (role_id, mask, resourceNode_id)
              SELECT {$role->getId()}, 1, resource.id FROM claro_resource_node resource
              WHERE resource.workspace_id = {$role->getWorkspace()->getId()}
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }
    }

    /**
     * @param PatchEvent $event
     */
    public function prePatch(PatchEvent $event)
    {
        /** @var Role $role */
        $role = $event->getObject();
        $user = $event->getValue();

        if (!$user->hasRole($role->getName())) {
            $this->dispatcher->dispatch('log', 'Log\LogRoleSubscribe', [$role, $user]);
        }
    }
}
