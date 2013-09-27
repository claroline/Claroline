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

    public function testNoLogMatchRules()
    {
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) {
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->andReturn(array());
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);
        $user                   = new User();

        $rule = new BadgeRule();

        /** @var badge $badge */
        $badge = new Badge();

        $this->assertEquals(false, $this->badgeRuleChecker->checkRule($rule, $user));
    }

    public function testOneLogMatchRules()
    {
        $log                    = new Log();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log) {
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->andReturn(array($log));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);
        $user                   = new User();

        $rule = new BadgeRule();

        $this->assertEquals(array($log), $this->badgeRuleChecker->checkRule($rule, $user));
    }

    public function testTwoLogMatchRules()
    {
        $log                    = new Log();
        $log2                   = new Log();
        $this->logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $log2) {
            $mock
                ->shouldReceive('findByBadgeRuleAndUser')
                ->andReturn(array($log, $log2));
        });
        $this->badgeRuleChecker = new BadgeRuleChecker($this->logRepository);
        $user                   = new User();

        $rule = new BadgeRule();

        $this->assertEquals(array($log, $log2), $this->badgeRuleChecker->checkRule($rule, $user));
    }
}
