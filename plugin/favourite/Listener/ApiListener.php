<?php

namespace HeVinci\FavouriteBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use HeVinci\FavouriteBundle\Manager\FavouriteManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var FavouriteManager */
    private $favouriteManager;

    /**
     * @DI\InjectParams({
     *     "favouriteManager" = @DI\Inject("hevinci.favourite.manager")
     * })
     *
     * @param FavouriteManager $favouriteManager
     */
    public function __construct(FavouriteManager $favouriteManager)
    {
        $this->favouriteManager = $favouriteManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Favourite nodes
        $favouriteCount = $this->favouriteManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[HeVinciFavouriteBundle] updated Favourite count: $favouriteCount");
    }
}
