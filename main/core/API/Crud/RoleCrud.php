<?php

namespace Claroline\CoreBundle\API\Crud;

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
     * @DI\Observe("crud_pre_create_object")
     *
     * @param \Claroline\CoreBundle\Event\CrudEvent $event
     */
    public function preCreate(CrudEvent $event)
    {
        if ($event->getObject() instanceof Role) {
            $role = $event->getObject();

            if (!$role->getWorkspace()) {
                $role->setName(strtoupper('role_'.$role->getTranslationKey()));
            }
        }
    }
}
