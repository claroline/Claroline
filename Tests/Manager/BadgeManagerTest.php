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
use \Mockery as m;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class BadgeManagerTest extends MockeryTestCase
{
    /** @var BadgeManager */
    private $manager;

    public function setUp()
    {
        parent::setUp();
        $entityManager = m::mock(
            'Doctrine\ORM\EntityManager',
            function ($mock) {
                $mock->shouldReceive('persist')
                    ->andReturn(null)
                    ->shouldReceive('flush')
                    ->andReturn(null);
            }
        );
        $this->manager = new BadgeManager($entityManager);
    }

    /**
     * @dataProvider userBadgeProvider
     *
     * @param array $users
     * @param $expectedBadgeAttributionCount
     */
    public function testAddBadgeToUsers(array $users, $expectedBadgeAttributionCount)
    {
        $this->assertEquals($expectedBadgeAttributionCount, $this->manager->addBadgeToUsers(new Badge(), $users));
    }

    public function userBadgeProvider()
    {
        return array(
            array(array($this->mockUser(false), $this->mockUser(false), $this->mockUser(false)), 3),
            array(array($this->mockUser(true), $this->mockUser(true), $this->mockUser(true)), 0),
            array(array($this->mockUser(false), $this->mockUser(true), $this->mockUser(false)), 2)
        );
    }

    private function mockUser($hasBadge)
    {
        return m::mock(
            'Claroline\CoreBundle\Entity\User[hasBadge]',
            function ($mock) use ($hasBadge) {
                $mock->shouldReceive('hasBadge')->andReturn($hasBadge);
            }
        );
    }
}
