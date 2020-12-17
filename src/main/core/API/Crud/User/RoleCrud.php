<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\DBAL\Driver\Connection;

class RoleCrud
{
    /** @var Connection */
    private $conn;

    /**
     * RoleCrud constructor.
     *
     * @param Connection $conn
     */
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

        if (Role::WS_ROLE === $role->getType() && $role->getWorkspace()) {
            // give open access to all the workspace resource
            $this->conn
                ->prepare("
                    INSERT INTO claro_resource_rights (role_id, mask, resourceNode_id)
                    SELECT {$role->getId()}, 1, resource.id FROM claro_resource_node resource
                    WHERE resource.workspace_id = {$role->getWorkspace()->getId()}
                ")
                ->execute();

            // init access rights for the workspace tools
            $this->conn
                ->prepare("
                    INSERT INTO claro_tool_rights (role_id, mask, ordered_tool_id)
                    SELECT {$role->getId()}, 0, ot.id 
                    FROM claro_ordered_tool AS ot
                    WHERE ot.workspace_id = {$role->getWorkspace()->getId()}
                ")
                ->execute();
        } elseif (Role::PLATFORM_ROLE === $role->getType()) {
            // init access rights for the desktop tools
            $this->conn
                ->prepare("
                    INSERT INTO claro_tool_rights (role_id, mask, ordered_tool_id)
                    SELECT {$role->getId()}, 0, ot.id 
                    FROM claro_ordered_tool AS ot
                    WHERE ot.workspace_id IS NULL AND user_id IS NULL
                ")
                ->execute();
        }
    }
}
