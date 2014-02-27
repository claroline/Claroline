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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\UserBadge;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class BadgeManagerTest extends MockeryTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \Claroline\CoreBundle\Repository\Badge\BadgeRepository */
    private $badgeRepository;

    public function setUp()
    {
        parent::setUp();

        $this->badgeRepository = $this->mock('Claroline\CoreBundle\Repository\Badge\BadgeRepository');
        $this->entityManager   = $this->mock('Doctrine\ORM\EntityManager');
        $this->entityManager->shouldReceive('persist')
            ->andReturn(null)
            ->shouldReceive('flush')
            ->andReturn(null);

        $this->eventDispatcher = $this->mock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * @dataProvider userBadgeProvider
     *
     * @param Badge       $badge
     * @param UserBadge[] $userBadges
     * @param User[]      $users
     * @param integer     $expectedBadgeAttributionCount
     */
    public function testAddBadgeToUsers(Badge $badge, $userBadges, array $users, $expectedBadgeAttributionCount)
    {
        $badgeRepository = $this->badgeRepository;
        $badgeRepository
            ->shouldReceive('findUserBadge')->once()
            ->with($badge, $users[0])
            ->andReturn($userBadges[0])
            ->shouldReceive('findUserBadge')->once()
            ->with($badge, $users[1])
            ->andReturn($userBadges[1])
            ->shouldReceive('findUserBadge')->once()
            ->with($badge, $users[2])
            ->andReturn($userBadges[2]);

        $entityManager = $this->entityManager;
        $entityManager->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Badge\Badge')
            ->andReturn($badgeRepository);

        $eventDispatcher = $this->eventDispatcher;
        $eventDispatcher
            ->shouldReceive('dispatch')
            ->andReturn(null);

        $manager = new BadgeManager($entityManager, $eventDispatcher);

        $this->assertEquals($expectedBadgeAttributionCount, $manager->addBadgeToUsers($badge, $users));
    }

    public function userBadgeProvider()
    {
        $badge     = new Badge();
        $user      = new User();
        $userBadge = new UserBadge();

        return array(
            array($badge, array(null, null, null), array($user, $user, $user), 3),

            array($badge, array($userBadge, null, null), array($user, $user, $user), 2),
            array($badge, array(null, $userBadge, null), array($user, $user, $user), 2),
            array($badge, array(null, null, $userBadge), array($user, $user, $user), 2),

            array($badge, array(null, $userBadge, $userBadge), array($user, $user, $user), 1),
            array($badge, array($userBadge, null, $userBadge), array($user, $user, $user), 1),
            array($badge, array($userBadge, $userBadge, null), array($user, $user, $user), 1),

            array($badge, array($userBadge, $userBadge, $userBadge), array($user, $user, $user), 0)
        );
    }
}
