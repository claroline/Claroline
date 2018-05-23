<?php

namespace Claroline\TagBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var TagManager */
    private $manager;

    /**
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
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Tag nodes
        $tagCount = $this->manager->replaceTagUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineTagBundle] updated Tag count: $tagCount");
    }
}
