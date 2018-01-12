<?php

namespace Claroline\CoreBundle\API\Crud\User;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Event\Crud\DeleteEvent;
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
     * @param WorkspaceManager $manager
     */
    public function __construct(WorkspaceManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("crud_pre_delete_object_claroline_corebundle_entity_organization_organization")
     *
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        /** @var Organization $organization */
        $organization = $event->getObject();
        if ($organization->isDefault()) {
            $event->block();

            // we can also throw an exception
        }
    }
}
