<?php

namespace Icap\BadgeBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Entity\BadgeRule;
use Icap\BadgeBundle\Entity\UserBadge;

class BadgeManagerTest extends MockeryTestCase
{
    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    /** @var \Doctrine\ORM\UnitOfWork */
    private $unitOfWork;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    /** @var \Icap\BadgeBundle\Repository\BadgeRepository */
    private $badgeRepository;

    public function setUp()
    {
        parent::setUp();

        $this->badgeRepository = $this->mock('Icap\BadgeBundle\Repository\BadgeRepository');
        $this->unitOfWork = $this->mock('Doctrine\ORM\UnitOfWork');
        $this->unitOfWork
            ->shouldReceive('computeChangeSets')
                ->andReturn(null);

        $this->entityManager = $this->mock('Doctrine\ORM\EntityManager');
        $this->entityManager
            ->shouldReceive('persist')
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
     * @param int         $expectedBadgeAttributionCount
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
            ->with('IcapBadgeBundle:Badge')
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
        $badge = new Badge();
        $user = new User();
        $userBadge = new UserBadge();

        return [
            [$badge, [null, null, null], [$user, $user, $user], 3],

            [$badge, [$userBadge, null, null], [$user, $user, $user], 2],
            [$badge, [null, $userBadge, null], [$user, $user, $user], 2],
            [$badge, [null, null, $userBadge], [$user, $user, $user], 2],

            [$badge, [null, $userBadge, $userBadge], [$user, $user, $user], 1],
            [$badge, [$userBadge, null, $userBadge], [$user, $user, $user], 1],
            [$badge, [$userBadge, $userBadge, null], [$user, $user, $user], 1],

            [$badge, [$userBadge, $userBadge, $userBadge], [$user, $user, $user], 0],
        ];
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

        return [
            [$badge1, new \DateTime('2014-02-02'), new \DateTime('2014-02-03')],
            [$badge2, new \DateTime('2014-02-02'), new \DateTime('2014-02-04')],
            [$badge3, new \DateTime('2014-02-02'), new \DateTime('2014-02-09')],
            [$badge4, new \DateTime('2014-02-02'), new \DateTime('2014-02-16')],
            [$badge5, new \DateTime('2014-02-02'), new \DateTime('2014-03-02')],
            [$badge6, new \DateTime('2014-02-02'), new \DateTime('2014-04-02')],
            [$badge7, new \DateTime('2014-02-02'), new \DateTime('2015-02-02')],
            [$badge8, new \DateTime('2014-02-02'), new \DateTime('2016-02-02')],
        ];
    }

    public function testIsRuleChangedWithOneRuleAndNoChange()
    {
        $rule1 = new BadgeRule();
        $rule1->setId(rand(0, PHP_INT_MAX));

        $rules = new ArrayCollection();
        $rules->add($rule1);

        $unitOfWork = $this->unitOfWork;
        $unitOfWork
            ->shouldReceive('getEntityChangeSet')->once()
                ->with($rule1);
        $entityManager = $this->entityManager;
        $entityManager
            ->shouldReceive('getUnitOfWork')
                ->andReturn($unitOfWork);

        $manager = new BadgeManager($entityManager, $this->eventDispatcher);

        $this->assertFalse($manager->isRuleChanged($rules, $rules));
    }

    public function testIsRuleChangedWitOneRuleAndOneNewRule()
    {
        $originalRule1 = new BadgeRule();
        $originalRule1->setId(rand(0, PHP_INT_MAX));

        $originalRules = new ArrayCollection();
        $originalRules->add($originalRule1);

        $newRule1 = new BadgeRule();

        $newRules = new ArrayCollection();
        $newRules->add($originalRule1);
        $newRules->add($newRule1);

        $unitOfWork = $this->unitOfWork;
        $unitOfWork
            ->shouldReceive('getEntityChangeSet')->once()
                ->with($originalRule1)
                ->andReturn([]);
        $entityManager = $this->entityManager;
        $entityManager
            ->shouldReceive('getUnitOfWork')
                ->andReturn($unitOfWork);

        $manager = new BadgeManager($entityManager, $this->eventDispatcher);

        $this->assertTrue($manager->isRuleChanged($newRules, $originalRules));
    }

    public function testIsRuleChangedWitOneRuleChanged()
    {
        $originalRule1 = new BadgeRule();
        $originalRule1->setId(rand(0, PHP_INT_MAX));

        $originalRules = new ArrayCollection();
        $originalRules->add($originalRule1);

        $newRules = new ArrayCollection();
        $newRules->add($originalRule1);

        $unitOfWork = $this->unitOfWork;
        $unitOfWork
            ->shouldReceive('getEntityChangeSet')->once()
                ->with($originalRule1)
                ->andReturn(['action' => [uniqid(), uniqid()]]);
        $entityManager = $this->entityManager;
        $entityManager
            ->shouldReceive('getUnitOfWork')
                ->andReturn($unitOfWork);

        $manager = new BadgeManager($entityManager, $this->eventDispatcher);

        $this->assertTrue($manager->isRuleChanged($newRules, $originalRules));
    }
}
