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

    public function testCheckRuleOneRuleMatchNoLog()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array());
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertFalse($this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleMatchOneLog()
    {
        $log                    = new Log();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleMatchTwoLog()
    {
        $log                    = new Log();
        $log2                   = new Log();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $user, $log, $log2) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log, $log2));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log, $log2), $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckBadgeTwoRuleMatchNoLog()
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

    public function testCheckBadgeTwoRuleMatchOneLog()
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

    public function testCheckBadgeTwoRuleMatchTwoLog()
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

    public function testCheckBadgeNoRule()
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

    public function testCheckBadgeOneRuleMatchNoLogOnWorkspace()
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

    public function testCheckBadgeOneRuleMatchOneLogOnWorkspace()
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

    public function testCheckBadgeOneRuleMatchTwoLogOnWorkspace()
    {
        $workspace              = new SimpleWorkspace();
        $log                    = new Log();
        $log2                   = new Log();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($workspace, $badgeRule, $user, $log, $log2) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with($workspace, $badgeRule, $user)
                ->andReturn(array($log, $log2));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log, $log2), $this->badgeRuleChecker->checkRule($workspace, $badgeRule, $user));
    }

    public function testCheckBadgeTwoRuleMatchNoLogOnWorkspace()
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

    public function testCheckBadgeTwoRuleMatchOneLogOnWorkspace()
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

    public function testCheckBadgeTwoRuleMatchTwoLogOnWorkspace()
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

    public function testCheckRuleOneRuleWithResultComparisonEqualMatchNoLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 11));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(0);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(false, $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonEqualMatchOneLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 12));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(0);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonSuperiorMatchNoLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 11));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(3);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(false, $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonSuperiorMatchOneLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 13));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(3);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonSuperiorEqualMatchNoLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 11));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(4);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(false, $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonSuperiorEqualMatchOneLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 12));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(4);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonSuperiorEqualMatchOneLog2()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 13));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(4);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonInferiorMatchNoLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 12));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(1);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(false, $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonInferiorMatchNoLog2()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 13));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(1);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(false, $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonInferiorMatchOneLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 11));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(1);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonInferiorEqualMatchNoLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 13));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(2);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(false, $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonInferiorEqualMatchOneLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 12));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(2);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }

    public function testCheckRuleOneRuleWithResultComparisonInferiorEqualMatchOneLog2()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 11));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(2);

        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByWorkspaceBadgeRuleAndUser')
                ->with(null, $badgeRule, $user)
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule(null, $badgeRule, $user));
    }
}
