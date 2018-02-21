<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Entity\Role;
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
}
