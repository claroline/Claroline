<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\UserBadge;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.badge")
 */
class BadgeManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "entityManager" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Badge                               $badge
     * @param \Claroline\CoreBundle\Entity\User[] $users
     *
     * @return int
     */
    public function addBadgeToUsers(Badge $badge, $users)
    {
        $addedBadge = 0;

        foreach ($users as $user) {
            if (!$user->hasBadge($badge)) {
                $addedBadge++;

                $userBadge = new UserBadge();
                $userBadge
                    ->setBadge($badge)
                    ->setUser($user);
                $badge->addUserBadge($userBadge);
            }
        }

        $this->entityManager->persist($badge);
        $this->entityManager->flush();

        return $addedBadge;
    }
}
