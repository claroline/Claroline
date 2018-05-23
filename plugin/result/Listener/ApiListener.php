<?php

namespace Claroline\ResultBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\ResultBundle\Manager\MarkManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var MarkManager */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.result.mark_manager")
     * })
     *
     * @param MarkManager $manager
     */
    public function __construct(MarkManager $manager)
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
        // Replace user of Mark nodes
        $markCount = $this->manager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineResultBundle] updated Mark count: $markCount");
    }
}
