<?php

namespace Claroline\BadgeBundle\Manager;

use Claroline\BadgeBundle\Entity\Badge;
use Claroline\BadgeBundle\Entity\UserBadge;
use Claroline\BadgeBundle\Repository\BadgeRepository;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.badge")
 */
class BadgeManager
{
    /**
     * @var \Claroline\BadgeBundle\Repository\BadgeRepository
     */
    protected $badgeRepository;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "badgeRepository" = @DI\Inject("badge_repository"),
     *     "entityManager"   = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(BadgeRepository $badgeRepository, EntityManager $entityManager)
    {
        $this->badgeRepository = $badgeRepository;
        $this->entityManager   = $entityManager;
    }

    /**
     * @param Badge $badge
     * @param \Claroline\CoreBundle\Entity\User[] $users
     *
     * @return int
     */
    public function addBadgeToUsers(Badge $badge, $users)
    {
        $addedBadge = 0;

        foreach($users as $user)
        {
            if(!$user->hasBadge($badge)) {
                $addedBadge++;

                $userBadge = new UserBadge();
                $userBadge
                    ->setBadge($badge)
                    ->setUser($user)
                ;
                $badge->addUserBadge($userBadge);
            }
        }

        $this->entityManager->persist($badge);
        $this->entityManager->flush();

        return $addedBadge;
    }
}
