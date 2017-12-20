<?php

namespace Claroline\CoreBundle\API\Crud;

use Claroline\CoreBundle\Event\CrudEvent;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.crud.workspace")
 * @DI\Tag("claroline.crud")
 */
class WorkspaceCrud
{
    /**
     * WorkspaceCrud constructor.
     *
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
     * @DI\Observe("crud_pre_delete_object_claroline_corebundle_entity_workspace_workspace")
     *
     * @param CrudEvent $event
     */
    public function preDelete(CrudEvent $event)
    {
        $this->manager->deleteWorkspace($event->getObject());
    }
}
