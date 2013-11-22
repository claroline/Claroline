<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Rule;

use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use \Mockery as m;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class RuleValidatorTest extends MockeryTestCase
{
    public function testValidateRuleOneRuleMatchNoLog()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array());
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleMatchOneLog()
    {
        $log                    = new Log();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleMatchTwoLog()
    {
        $log                    = new Log();
        $log2                   = new Log();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $user, $log, $log2) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log, $log2));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log, $log2), $ruleValidator->validateRule($badgeRule, $user));
    }

    public function testCheckBadgeTwoRuleMatchNoLog()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array());
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule2, $user, array())
                ->andReturn(array());
        });
        $ruleValidator = new Validator($logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge
            ->setRules($badgeRules);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testCheckBadgeTwoRuleMatchOneLog()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array(new Log()));
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule2, $user, array())
                ->andReturn(array());
        });
        $ruleValidator = new Validator($logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge->setRules($badgeRules);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testCheckBadgeTwoRuleMatchTwoLog()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $log                    = new Log();
        $log2                   = new Log();
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user, $log, $log2) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule2, $user, array())
                ->andReturn(array($log2));
        });
        $ruleValidator = new Validator($logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge->setRules($badgeRules);

        $this->assertEquals(array($log, $log2), $ruleValidator->validate($badge, $user));
    }

    public function testCheckBadgeNoRule()
    {
        $user                   = new User();
        $log                    = new Log();
        $log2                   = new Log();
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($user, $log, $log2) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->never();
        });
        $ruleValidator = new Validator($logRepository);

        /** @var badge $badge */
        $badge = new Badge();

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testCheckBadgeOneRuleMatchNoLogOnWorkspace()
    {
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $restriction            = array('workspace' => new SimpleWorkspace());
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $user, $restriction) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, $restriction)
                ->andReturn(array());
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($badgeRule, $user, $restriction));
    }

    public function testCheckBadgeOneRuleMatchOneLogOnWorkspace()
    {
        $log                    = new Log();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $restriction            = array('workspace' => new SimpleWorkspace());
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user, $restriction) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, $restriction)
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($badgeRule, $user, $restriction));
    }

    public function testCheckBadgeOneRuleMatchTwoLogOnWorkspace()
    {
        $log                    = new Log();
        $log2                   = new Log();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $restriction            = array('workspace' => new SimpleWorkspace());
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $user, $log, $log2, $restriction) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, $restriction)
                ->andReturn(array($log, $log2));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log, $log2), $ruleValidator->validateRule($badgeRule, $user, $restriction));
    }

    public function testCheckBadgeTwoRuleMatchNoLogOnWorkspace()
    {
        $workspace              = new SimpleWorkspace();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $restriction            = array('workspace' => $workspace);
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user, $restriction) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, $restriction)
                ->andReturn(array());
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule2, $user, $restriction)
                ->andReturn(array());
        });
        $ruleValidator = new Validator($logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge
            ->setRules($badgeRules)
            ->setWorkspace($workspace);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testCheckBadgeTwoRuleMatchOneLogOnWorkspace()
    {
        $workspace              = new SimpleWorkspace();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $restriction            = array('workspace' => $workspace);
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user, $restriction) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, $restriction)
                ->andReturn(array(new Log()));
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule2, $user, $restriction)
                ->andReturn(array());
        });
        $ruleValidator = new Validator($logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge
            ->setRules($badgeRules)
            ->setWorkspace($workspace);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testCheckBadgeTwoRuleMatchTwoLogOnWorkspace()
    {
        $workspace              = new SimpleWorkspace();
        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule2             = new BadgeRule();
        $log                    = new Log();
        $log2                   = new Log();
        $restriction            = array('workspace' => $workspace);
        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $badgeRule2, $user, $log, $log2, $restriction) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, $restriction)
                ->andReturn(array($log));
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule2, $user, $restriction)
                ->andReturn(array($log2));
        });
        $ruleValidator = new Validator($logRepository);

        $badgeRules = array($badgeRule, $badgeRule2);

        /** @var badge $badge */
        $badge = new Badge();
        $badge
            ->setRules($badgeRules)
            ->setWorkspace($workspace);

        $this->assertEquals(array($log, $log2), $ruleValidator->validate($badge, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonEqualMatchNoLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 11));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(0);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonEqualMatchOneLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 12));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(0);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonSuperiorMatchNoLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 11));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(3);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonSuperiorMatchOneLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 13));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(3);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonSuperiorEqualMatchNoLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 11));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(4);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonSuperiorEqualMatchOneLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 12));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(4);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonSuperiorEqualMatchOneLog2()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 13));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(4);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonInferiorMatchNoLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 12));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(1);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonInferiorMatchNoLog2()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 13));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(1);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonInferiorMatchOneLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 11));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(1);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonInferiorEqualMatchNoLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 13));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(2);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonInferiorEqualMatchOneLog()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 12));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(2);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleWithResultComparisonInferiorEqualMatchOneLog2()
    {
        $log                    = new Log();
        $log->setDetails(array('result' => 11));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule
            ->setResult('12')
            ->setResultComparison(2);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleMatchOneLogOnWrongResource()
    {
        $resourceNode = new ResourceNode();
        $resourceNode->setId($resourceNodeId = rand(10, PHP_INT_MAX));

        $otherResourceNode = new ResourceNode();
        $otherResourceNode->setId($otherResourceNodeId = rand(0, 10));

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule->setResource($otherResourceNode);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array());
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($badgeRule, $user));
    }

    public function testValidateRuleOneRuleMatchOneLogOnRightResource()
    {
        $resourceNode = new ResourceNode();
        $resourceNode->setId($resourceNodeId = rand(10, PHP_INT_MAX));

        $log                    = new Log();
        $log->setResourceNode($resourceNode);

        $user                   = new User();
        $badgeRule              = new BadgeRule();
        $badgeRule->setResource($resourceNode);

        $logRepository    = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository', function($mock) use($log, $badgeRule, $user) {
            $mock
                ->shouldReceive('findByRuleAndUser')
                ->with($badgeRule, $user, array())
                ->andReturn(array($log));
        });
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($badgeRule, $user));
    }
}
