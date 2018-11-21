<?php

namespace Claroline\TagBundle\Listener\User;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class GroupListener
{
    /** @var TagManager */
    private $manager;

    /**
     * GroupListener constructor.
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
     * @DI\Observe("claroline_groups_delete")
     *
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
