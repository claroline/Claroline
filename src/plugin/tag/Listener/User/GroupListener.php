<?php

namespace Claroline\TagBundle\Listener\User;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\TagBundle\Manager\TagManager;

class GroupListener
{
    /** @var TagManager */
    private $manager;

    /**
     * GroupListener constructor.
     *
     * @param TagManager $manager
     */
    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param GenericDataEvent $event
     */
    public function onDelete(GenericDataEvent $event)
    {
        /** @var Group[] $groups */
        $groups = $event->getData();

        $ids = [];
        foreach ($groups as $group) {
            $ids[] = $group->getId();
        }

        $this->manager->removeTaggedObjectsByClassAndIds(Group::class, $ids);
    }
}
