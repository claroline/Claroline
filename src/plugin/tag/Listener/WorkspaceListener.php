<?php

namespace Claroline\TagBundle\Listener;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TagBundle\Manager\TagManager;

class WorkspaceListener
{
    /** @var TagManager */
    private $manager;

    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    public function onDelete(DeleteEvent $event)
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        $this->manager->removeTaggedObjectsByClassAndIds(Workspace::class, [$workspace->getId()]);
    }
}
