<?php

namespace Claroline\BadgeBundle\Manager;

use Claroline\BadgeBundle\Entity\Badge;
use \Mockery as m;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class BadgeManagerTest extends MockeryTestCase
{
    /** @var BadgeManager */
    private $manager;
    private $badgeRepository;
    private $entityManager;

    public function setUp()
    {
        parent::setUp();

        $this->badgeRepository = m::mock('Claroline\BadgeBundle\Repository\BadgeRepository');
        $this->entityManager   = m::mock('Doctrine\ORM\EntityManager', function($mock) {
            $mock
                ->shouldReceive('persist')
                ->andReturn(null)
                ->shouldReceive('flush')
                ->andReturn(null);
            ;
        });
        $this->manager         = new BadgeManager($this->badgeRepository, $this->entityManager);
    }

    public function testAddBadgeTo3Users()
    {
        $user1     = m::mock('Claroline\CoreBundle\Entity\User[hasBadge]', function($mock) {
            $mock->shouldReceive('hasBadge')->andReturn(false);
        });
        $user2     = m::mock('Claroline\CoreBundle\Entity\User[hasBadge]', function($mock) {
            $mock->shouldReceive('hasBadge')->andReturn(false);
        });
        $user3     = m::mock('Claroline\CoreBundle\Entity\User[hasBadge]', function($mock) {
            $mock->shouldReceive('hasBadge')->andReturn(false);
        });

        $users = array($user1, $user2, $user3);

        /** @var badge $badge */
        $badge = new Badge();

        $this->assertEquals(3, $this->manager->addBadgeToUsers($badge, $users));
    }

    public function testAddBadgeTo0Users()
    {
        $user1     = m::mock('Claroline\CoreBundle\Entity\User[hasBadge]', function($mock) {
            $mock->shouldReceive('hasBadge')->andReturn(true);
        });
        $user2     = m::mock('Claroline\CoreBundle\Entity\User[hasBadge]', function($mock) {
            $mock->shouldReceive('hasBadge')->andReturn(true);
        });
        $user3     = m::mock('Claroline\CoreBundle\Entity\User[hasBadge]', function($mock) {
            $mock->shouldReceive('hasBadge')->andReturn(true);
        });

        $users = array($user1, $user2, $user3);

        /** @var badge $badge */
        $badge = new Badge();

        $this->assertEquals(0, $this->manager->addBadgeToUsers($badge, $users));
    }

    public function testAddBadgeTo2Users1AlreadyHave()
    {
        $user1     = m::mock('Claroline\CoreBundle\Entity\User[hasBadge]', function($mock) {
            $mock->shouldReceive('hasBadge')->andReturn(false);
        });
        $user2     = m::mock('Claroline\CoreBundle\Entity\User[hasBadge]', function($mock) {
            $mock->shouldReceive('hasBadge')->andReturn(true);
        });
        $user3     = m::mock('Claroline\CoreBundle\Entity\User[hasBadge]', function($mock) {
            $mock->shouldReceive('hasBadge')->andReturn(false);
        });

        $users = array($user1, $user2, $user3);

        /** @var badge $badge */
        $badge = new Badge();

        $this->assertEquals(2, $this->manager->addBadgeToUsers($badge, $users));
    }
}
