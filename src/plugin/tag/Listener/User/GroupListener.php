<?php

namespace Claroline\TagBundle\Listener\User;

use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\CoreBundle\Entity\Group;
use Claroline\TagBundle\Manager\TagManager;

class GroupListener
{
    /** @var TagManager */
    private $manager;

    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    public function onDelete(DeleteEvent $event)
    {
        /** @var Group $group */
        $group = $event->getObject();

        $this->manager->removeTaggedObjectsByClassAndIds(Group::class, [$group->getId()]);
    }
}
