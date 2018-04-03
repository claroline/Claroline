<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\PatchEvent;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Role;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.role")
 * @DI\Tag("claroline.crud")
 */
class RoleCrud
{
    /**
     * @DI\InjectParams({
     *     "dispatcher" = @DI\Inject("claroline.event.event_dispatcher")
     * })
     *
     * @param StrictDispatcher $dispatcher
     */
    public function __construct(StrictDispatcher $dispatcher)
    {
        //too many dependencies, simplify this when we can
        $this->dispatcher = $dispatcher;
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
