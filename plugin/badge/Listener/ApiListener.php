<?php

namespace Icap\BadgeBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Icap\BadgeBundle\Manager\BadgeClaimManager;
use Icap\BadgeBundle\Manager\BadgeManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var BadgeManager */
    private $badgeManager;

    /** @var BadgeClaimManager */
    private $badgeClaimManager;

    /**
     * @DI\InjectParams({
     *     "badgeManager" = @DI\Inject("icap_badge.manager.badge"),
     *     "badgeClaimManager" = @DI\Inject("icap_badge.manager.badge_claim")
     * })
     *
     * @param BadgeManager      $badgeManager
     * @param BadgeClaimManager $badgeClaimManager
     */
    public function __construct(BadgeManager $badgeManager, BadgeClaimManager $badgeClaimManager)
    {
        $this->badgeManager = $badgeManager;
        $this->badgeClaimManager = $badgeClaimManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of BadgeClaim nodes
        $badgeClaimCount = $this->badgeClaimManager->replaceBadgeClaimUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapBadgeBundle] updated BadgeClaim count: $badgeClaimCount");

        // Replace user of BadgeCollection nodes
        $badgeCollectionCount = $this->badgeManager->replaceBadgeCollectionUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapBadgeBundle] updated BadgeCollection count: $badgeCollectionCount");

        // Replace user of UserBadge nodes
        $userBadgeCount = $this->badgeManager->replaceUserBadgeUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapBadgeBundle] updated UserBadge count: $userBadgeCount");
    }
}
