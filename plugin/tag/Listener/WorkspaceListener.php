<?php

namespace Claroline\TagBundle\Listener;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class WorkspaceListener
{
    /** @var TagManager */
    private $manager;

    /**
     * WorkspaceListener constructor.
     *
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.tag_manager")
     * })
     *
     * @param TagManager $manager
     */
    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("claroline_workspaces_delete")
     *
     * @param GenericDataEvent $event
     */
    public function onDelete(GenericDataEvent $event)
    {
        /** @var Workspace[] $workspaces */
        $workspaces = $event->getData();

        $ids = [];
        foreach ($workspaces as $workspace) {
            $ids[] = $workspace->getId();
        }

        $this->manager->removeTaggedObjectsByClassAndIds(Workspace::class, $ids);
    }
}
