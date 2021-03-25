<?php

namespace HeVinci\FavouriteBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use HeVinci\FavouriteBundle\Manager\FavouriteManager;

/**
 * Class UserListener.
 */
class UserListener
{
    /** @var FavouriteManager */
    private $favouriteManager;

    public function __construct(FavouriteManager $favouriteManager)
    {
        $this->favouriteManager = $favouriteManager;
    }

    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Favourite nodes
        $favouriteCount = $this->favouriteManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[HeVinciFavouriteBundle] updated Favourite count: $favouriteCount");
    }
}
