<?php

namespace Claroline\CoreBundle\API\Crud;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Event\CrudEvent;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.organization")
 * @DI\Tag("claroline.crud")
 */
class OrganizationCrud
{
    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(WorkspaceManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("crud_pre_delete_object")
     *
     * @param \Claroline\CoreBundle\Event\CrudEvent $event
     */
    public function preDelete(CrudEvent $event)
    {
        if ($event->getObject() instanceof Organization) {
            if ($event->getObject()->isDefault()) {
                $event->block();
                //we can also throw an exception
            }
        }
    }
}
