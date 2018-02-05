<?php

namespace Claroline\CoreBundle\API\Crud;

use Claroline\CoreBundle\API\Options;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Crud\CopyEvent;
use Claroline\CoreBundle\Event\Crud\CreateEvent;
use Claroline\CoreBundle\Event\Crud\DeleteEvent;
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
     * @param DeleteEvent $event
     */
    public function preDelete(DeleteEvent $event)
    {
        $this->manager->deleteWorkspace($event->getObject());
    }

    /**
     * @DI\Observe("crud_pre_create_object_claroline_corebundle_entity_workspace_workspace")
     *
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        $workspace = $this->manager->createWorkspace($event->getObject());

        return $this->manager->copy($this->manager->getDefaultModel(), $workspace, false);
    }

    /**
     * @DI\Observe("crud_pre_copy_object_claroline_corebundle_entity_workspace_workspace")
     *
     * @param CopyEvent $event
     */
    public function preCopy(CopyEvent $event)
    {
        $workspace = $event->getObject();
        $options = $event->getOptions();

        $new = $event->getCopy();
        $new->setName($workspace->getName());
        $new->setCode($workspace->getCode());

        $this->manager->copy($workspace, $new, in_array(Options::WORKSPACE_MODEL, $options));
    }
}
