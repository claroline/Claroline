<?php

namespace UJM\ExoBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Manager\Item\ShareManager;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var ItemManager */
    private $itemManager;

    /** @var ShareManager */
    private $sharedManager;

    /** @var PaperManager */
    private $paperManager;

    /**
     * @DI\InjectParams({
     *     "itemManager"   = @DI\Inject("ujm_exo.manager.item"),
     *     "sharedManager" = @DI\Inject("ujm_exo.manager.share"),
     *     "paperManager"  = @DI\Inject("ujm_exo.manager.paper")
     * })
     *
<<<<<<< Updated upstream
     * @param ItemManager     $itemManager
     * @param ShareManager    $sharedManager
     * @param PaperManager    $paperManager
=======
     * @param ItemManager  $itemManager
     * @param ShareManager $sharedManager
     * @param PaperManager $paperManager
>>>>>>> Stashed changes
     */
    public function __construct(
        ItemManager $itemManager,
        ShareManager $sharedManager,
        PaperManager $paperManager
    ) {
        $this->itemManager = $itemManager;
        $this->sharedManager = $sharedManager;
        $this->paperManager = $paperManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Item nodes
        $itemCount = $this->itemManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[UJMExoBundle] updated Item count: $itemCount");

        // Replace user of Shared nodes
        $sharedCount = $this->sharedManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[UJMExoBundle] updated Shared count: $sharedCount");

        // Replace user of Paper nodes
        $paperCount = $this->paperManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[UJMExoBundle] updated Paper count: $paperCount");
    }
}
