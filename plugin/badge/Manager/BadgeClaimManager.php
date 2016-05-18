<?php

namespace Icap\BadgeBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_badge.manager.badge_claim")
 */
class BadgeClaimManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "entityManager"   = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param User $user
     *
     * @return \Icap\BadgeBundle\Entity\BadgeClaim[]
     */
    public function getByUser(User $user)
    {
        /** @var \Icap\BadgeBundle\Entity\BadgeClaim[] $claimedBadges */
        $claimedBadges = [];

        /** @var \Icap\BadgeBundle\Entity\BadgeClaim[] $badgeClaims */
        $badgeClaims = $this->entityManager->getRepository('IcapBadgeBundle:BadgeClaim')->findByUser($user);

        foreach ($badgeClaims as $badgeClaim) {
            $claimedBadges[$badgeClaim->getBadge()->getId()] = $badgeClaim;
        }

        return $claimedBadges;
    }
}
