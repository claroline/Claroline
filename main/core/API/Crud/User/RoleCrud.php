<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Event\CrudEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.role")
 * @DI\Tag("claroline.crud")
 */
class RoleCrud
{
    /**
     * @DI\Observe("crud_pre_create_object_claroline_corebundle_entity_role")
     *
     * @param CrudEvent $event
     */
    public function preCreate(CrudEvent $event)
    {
        /** @var Role $role */
        $role = $event->getObject();

        if (!$role->getWorkspace()) {
            $role->setName(strtoupper('role_'.$role->getTranslationKey()));
        }
    }

    /**
     * @DI\Observe("crud_pre_patch_object_claroline_corebundle_entity_role")
     *
     * @param CrudEvent $event
     */
    public function prePatch(CrudEvent $event)
    {
    }
}
