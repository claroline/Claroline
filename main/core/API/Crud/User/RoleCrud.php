<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\DBAL\Driver\Connection;

class RoleCrud
{
    public function __construct(Connection $conn)
    {
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
}
