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
     * @dataProvider testAddBadgeToUsersProvider
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

    public function testAddBadgeToUsersProvider()
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

    /**
     * @dataProvider testGenerateExpiredDateProvider
     *
     * @param Badge     $badge
     * @param \DateTime $currentDate
     * @param \DateTime $expecteDate
     */
    public function testGenerateExpiredDate($badge, $currentDate, $expecteDate)
    {
        $manager = new BadgeManager($this->entityManager, $this->eventDispatcher);

        $this->assertEquals($expecteDate, $manager->generateExpireDate($badge, $currentDate));
    }

    public function testGenerateExpiredDateProvider()
    {
        $badge1 = new badge();
        $badge1
            ->setExpireDuration(1)
            ->setExpirePeriod(Badge::EXPIRE_PERIOD_DAY);

        $badge2 = new badge();
        $badge2
            ->setExpireDuration(2)
            ->setExpirePeriod(Badge::EXPIRE_PERIOD_DAY);

        $badge3 = new badge();
        $badge3
            ->setExpireDuration(1)
            ->setExpirePeriod(Badge::EXPIRE_PERIOD_WEEK);

        $badge4 = new badge();
        $badge4
            ->setExpireDuration(2)
            ->setExpirePeriod(Badge::EXPIRE_PERIOD_WEEK);

        $badge5 = new badge();
        $badge5
            ->setExpireDuration(1)
            ->setExpirePeriod(Badge::EXPIRE_PERIOD_MONTH);

        $badge6 = new badge();
        $badge6
            ->setExpireDuration(2)
            ->setExpirePeriod(Badge::EXPIRE_PERIOD_MONTH);

        $badge7 = new badge();
        $badge7
            ->setExpireDuration(1)
            ->setExpirePeriod(Badge::EXPIRE_PERIOD_YEAR);

        $badge8 = new badge();
        $badge8
            ->setExpireDuration(2)
            ->setExpirePeriod(Badge::EXPIRE_PERIOD_YEAR);

        return array(
            array($badge1, new \DateTime('2014-02-02'), new \DateTime('2014-02-03')),
            array($badge2, new \DateTime('2014-02-02'), new \DateTime('2014-02-04')),
            array($badge3, new \DateTime('2014-02-02'), new \DateTime('2014-02-09')),
            array($badge4, new \DateTime('2014-02-02'), new \DateTime('2014-02-16')),
            array($badge5, new \DateTime('2014-02-02'), new \DateTime('2014-03-02')),
            array($badge6, new \DateTime('2014-02-02'), new \DateTime('2014-04-02')),
            array($badge7, new \DateTime('2014-02-02'), new \DateTime('2015-02-02')),
            array($badge8, new \DateTime('2014-02-02'), new \DateTime('2016-02-02'))
        );
    }
}
