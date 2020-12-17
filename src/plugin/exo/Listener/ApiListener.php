<?php

namespace UJM\ExoBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Manager\Item\ShareManager;

/**
 * Class ApiListener.
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
     * @param ItemManager  $itemManager
     * @param ShareManager $sharedManager
     * @param PaperManager $paperManager
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
