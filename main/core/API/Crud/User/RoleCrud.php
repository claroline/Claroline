<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\DBAL\Driver\Connection;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.role")
 * @DI\Tag("claroline.crud")
 */
class RoleCrud
{
    /**
     * @DI\InjectParams({
     *     "dispatcher" = @DI\Inject("claroline.event.event_dispatcher"),
     *     "conn"       = @DI\Inject("doctrine.dbal.default_connection")
     * })
     *
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
     * @DI\Observe("crud_pre_create_object_claroline_corebundle_entity_role")
     *
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
     * @DI\Observe("crud_post_create_object_claroline_corebundle_entity_role")
     *
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
     * @DI\Observe("crud_pre_patch_object_claroline_corebundle_entity_role")
     *
     * @param PatchEvent $event
     */
    public function prePatch(PatchEvent $event)
    {
        /** @var Role $role */
        $role = $event->getObject();
        $users = $event->getValue();

        foreach ($users as $user) {
            if (!$user->hasRole($role->getName())) {
                $this->dispatcher->dispatch('log', 'Log\LogRoleSubscribe', [$role, $user]);
            }
        }
    }
}
