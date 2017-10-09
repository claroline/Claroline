<?php

namespace Claroline\CoreBundle\API\Crud;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
        if ($event->getObject() instanceof Workspace) {
            $this->manager->deleteWorkspace($event->getObject());
        }
    }
}
