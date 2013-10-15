<?php

namespace Claroline\CoreBundle\Badge;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
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
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $rule, $user)
                ->andReturn(array());
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertFalse($this->badgeRuleChecker->checkRule(null, $rule, $user));
    }

    public function testOneRuleMatchOneLog()
    {
        $log                    = new Log();
        $user                   = new User();
        $rule                   = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $rule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $rule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule(null, $rule, $user));
    }

    public function testOneRuleMatchTwoLog()
    {
        $log                    = new Log();
        $log2                   = new Log();
        $user                   = new User();
        $rule                   = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($rule, $user, $log, $log2) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $rule, $user)
                ->andReturn(array($log, $log2));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log, $log2), $this->badgeRuleChecker->checkRule(null, $rule, $user));
    }

    public function testTwoRuleMatchNoLog()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array());
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule2, $user)
                ->andReturn(array());
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge
            ->setBadgeRules($badgeRules);

        $this->assertFalse($this->badgeRuleChecker->checkBadge($badge, $user));
    }

    public function testTwoRuleMatchOneLog()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array(new Log()));
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule2, $user)
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
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule2, $user)
                ->andReturn(array($log2));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge->setBadgeRules($badgeRules);

        $this->assertEquals(array($log, $log2), $this->badgeRuleChecker->checkBadge($badge, $user));
    }

    public function testNoRule()
    {
        $user                   = new User();
        $log                    = new Log();
        $log2                   = new Log();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($user, $log, $log2) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->never();
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        /** @var badge $badge */
        $badge = new Badge();

        $this->assertEquals(false, $this->badgeRuleChecker->checkBadge($badge, $user));
    }

    public function testOneRuleMatchNoLogOnWorkspace()
    {
        $workspace              = new SimpleWorkspace();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($workspace, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with($workspace, $badgeRule, $user)
                ->andReturn(array());
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertFalse($this->badgeRuleChecker->checkRule($workspace, $badgeRule, $user));
    }

    public function testOneRuleMatchOneLogOnWorkspace()
    {
        $workspace              = new SimpleWorkspace();
        $log                    = new Log();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($workspace, $log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with($workspace, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule($workspace, $badgeRule, $user));
    }

    public function testOneRuleMatchTwoLogOnWorkspace()
    {
        $workspace              = new SimpleWorkspace();
        $log                    = new Log();
        $log2                   = new Log();
        $user                   = new User();
        $badgeRule                   = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($workspace, $badgeRule, $user, $log, $log2) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with($workspace, $badgeRule, $user)
                ->andReturn(array($log, $log2));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log, $log2), $this->badgeRuleChecker->checkRule($workspace, $badgeRule, $user));
    }

    public function testTwoRuleMatchNoLogOnWorkspace()
    {
        $workspace              = new SimpleWorkspace();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($workspace, $badgeRule, $badgeRule2, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with($workspace, $badgeRule, $user)
                ->andReturn(array());
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with($workspace, $badgeRule2, $user)
                ->andReturn(array());
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge
            ->setBadgeRules($badgeRules)
            ->setWorkspace($workspace);

        $this->assertFalse($this->badgeRuleChecker->checkBadge($badge, $user));
    }

    public function testTwoRuleMatchOneLogOnWorkspace()
    {
        $workspace              = new SimpleWorkspace();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($workspace, $badgeRule, $badgeRule2, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with($workspace, $badgeRule, $user)
                ->andReturn(array(new Log()));
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with($workspace, $badgeRule2, $user)
                ->andReturn(array());
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge
            ->setBadgeRules($badgeRules)
            ->setWorkspace($workspace);

        $this->assertFalse($this->badgeRuleChecker->checkBadge($badge, $user));
    }

    public function testTwoRuleMatchTwoLogOnWorkspace()
    {
        $workspace              = new SimpleWorkspace();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $log                    = new Log();
        $log2                   = new Log();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($workspace, $badgeRule, $badgeRule2, $user, $log, $log2) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with($workspace, $badgeRule, $user)
                ->andReturn(array($log));
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with($workspace, $badgeRule2, $user)
                ->andReturn(array($log2));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge
            ->setBadgeRules($badgeRules)
            ->setWorkspace($workspace);

        $this->assertEquals(array($log, $log2), $this->badgeRuleChecker->checkBadge($badge, $user));
    }
}
