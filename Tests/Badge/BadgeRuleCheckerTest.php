<?php

namespace Claroline\CoreBundle\Badge;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;
use \Mockery as m;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class BadgeRuleCheckerTest extends MockeryTestCase
{
    /** @var BadgeRuleChecker */
    private $badgeRuleChecker;
    private $logRepository;
    private $entityManager;

    public function testOneRuleMatchNoLog()
    {
        $user                   = new User();
        $rule                   = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($rule, $user) {
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->with($rule, $user)
                ->andReturn(array());
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        /** @var badge $badge */
        $badge = new Badge();

        $this->assertFalse($this->badgeRuleChecker->checkRule($rule, $user));
    }

    public function testOneRuleMatchOneLog()
    {
        $log                    = new Log();
        $user                   = new User();
        $rule                   = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $rule, $user) {
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->with($rule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule($rule, $user));
    }

    public function testOneRuleMatchTwoLog()
    {
        $log                    = new Log();
        $log2                   = new Log();
        $user                   = new User();
        $rule                   = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($rule, $user, $log, $log2) {
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->with($rule, $user)
                ->andReturn(array($log, $log2));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log, $log2), $this->badgeRuleChecker->checkRule($rule, $user));
    }

    public function testTwoRuleMatchNoLog()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user) {
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->with($badgeRule, $user)
                ->andReturn(array());
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->with($badgeRule2, $user)
                ->andReturn(array());
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge->setBadgeRules($badgeRules);

        $this->assertFalse($this->badgeRuleChecker->checkBadge($badge, $user));
    }

    public function testTwoRuleMatchOneLog()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user) {
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->with($badgeRule, $user)
                ->andReturn(array(new Log()));
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->with($badgeRule2, $user)
                ->andReturn(array());
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge->setBadgeRules($badgeRules);

        $this->assertFalse($this->badgeRuleChecker->checkBadge($badge, $user));
    }

    public function testTwoRuleMatchTwoLog()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $log                    = new Log();
        $log2                   = new Log();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user, $log, $log2) {
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->with($badgeRule, $user)
                ->andReturn(array($log));
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->with($badgeRule2, $user)
                ->andReturn(array($log2));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge->setBadgeRules($badgeRules);

        $this->assertEquals(array(array($log), array($log2)), $this->badgeRuleChecker->checkBadge($badge, $user));
    }

    public function testNoRule()
    {
        $user                   = new User();
        $log                    = new Log();
        $log2                   = new Log();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($user, $log, $log2) {
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->never();
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        /** @var badge $badge */
        $badge = new Badge();

        $this->assertEquals(false, $this->badgeRuleChecker->checkBadge($badge, $user));
    }
}
