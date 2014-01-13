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

use \Mockery as m;
use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class RuleValidatorTest extends MockeryTestCase
{
    private $logRepo;
    private $validator;
    private $user;

    protected function setUp()
    {
        parent::setUp();
        $this->logRepo = m::mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $this->validator = new Validator($this->logRepo);
        $this->user = new User();
    }

    public function testValidateRuleOneRuleMatchNoLog()
    {
        $badgeRule = new BadgeRule();
        $this->mockFind($badgeRule, array(), array());
        $this->assertFalse($this->validator->validateRule($badgeRule, $this->user));
    }

    public function testValidateRuleOneRuleMatchOneLog()
    {
        $log = new Log();
        $badgeRule = new BadgeRule();
        $this->mockFind($badgeRule, array(), array($log));
        $this->assertEquals(array($log), $this->validator->validateRule($badgeRule, $this->user));
    }

    public function testValidateRuleOneRuleMatchTwoLog()
    {
        $logA = new Log();
        $logB = new Log();
        $badgeRule = new BadgeRule();
        $this->mockFind($badgeRule, array(), array($logA, $logB));
        $this->assertEquals(array($logA, $logB), $this->validator->validateRule($badgeRule, $this->user));
    }

    public function testCheckBadgeTwoRuleMatchNoLog()
    {
        $badgeRuleA = new BadgeRule();
        $badgeRuleB = new BadgeRule();
        $this->mockFind($badgeRuleA, array(), array());
        $this->mockFind($badgeRuleB, array(), array());
        $badge = new Badge();
        $badge->setRules(array($badgeRuleA, $badgeRuleB));
        $this->assertFalse($this->validator->validate($badge, $this->user));
    }

    public function testCheckBadgeTwoRuleMatchOneLog()
    {
        $badgeRuleA = new BadgeRule();
        $badgeRuleB = new BadgeRule();
        $this->mockFind($badgeRuleA, array(), array(new Log()));
        $this->mockFind($badgeRuleB, array(), array());
        $badge = new Badge();
        $badge->setRules(array($badgeRuleA, $badgeRuleB));
        $this->assertFalse($this->validator->validate($badge, $this->user));
    }

    public function testCheckBadgeTwoRuleMatchTwoLog()
    {
        $badgeRuleA = new BadgeRule();
        $badgeRuleB = new BadgeRule();
        $logA = new Log();
        $logB = new Log();
        $this->mockFind($badgeRuleA, array(), array($logA));
        $this->mockFind($badgeRuleB, array(), array($logB));
        $badge = new Badge();
        $badge->setRules(array($badgeRuleA, $badgeRuleB));
        $this->assertEquals(array($logA, $logB), $this->validator->validate($badge, $this->user));
    }

    public function testCheckBadgeNoRule()
    {
        $this->logRepo->shouldReceive('findByRuleAndUser')->never();
        $this->assertFalse($this->validator->validate(new Badge(), new User()));
    }

    public function testCheckBadgeOneRuleMatchNoLogOnWorkspace()
    {
        $badgeRule = new BadgeRule();
        $restrictions = array('workspace' => new SimpleWorkspace());
        $this->mockFind($badgeRule, $restrictions, array());
        $this->assertFalse($this->validator->validateRule($badgeRule, $this->user, $restrictions));
    }

    public function testCheckBadgeOneRuleMatchOneLogOnWorkspace()
    {
        $log = new Log();
        $badgeRule = new BadgeRule();
        $restrictions = array('workspace' => new SimpleWorkspace());
        $this->mockFind($badgeRule, $restrictions, array($log));
        $this->assertEquals(array($log), $this->validator->validateRule($badgeRule, $this->user, $restrictions));
    }

    public function testCheckBadgeOneRuleMatchTwoLogOnWorkspace()
    {
        $logA = new Log();
        $logB = new Log();
        $badgeRule = new BadgeRule();
        $restrictions = array('workspace' => new SimpleWorkspace());
        $this->mockFind($badgeRule, $restrictions, array($logA, $logB));
        $this->assertEquals(
            array($logA, $logB),
            $this->validator->validateRule($badgeRule, $this->user, $restrictions)
        );
    }

    public function testCheckBadgeTwoRuleMatchNoLogOnWorkspace()
    {
        $workspace = new SimpleWorkspace();
        $badgeRuleA = new BadgeRule();
        $badgeRuleB = new BadgeRule();
        $restrictions = array('workspace' => $workspace);
        $this->mockFind($badgeRuleA, $restrictions, array());
        $this->mockFind($badgeRuleB, $restrictions, array());
        $badge = new Badge();
        $badge->setRules(array($badgeRuleA, $badgeRuleB))
            ->setWorkspace($workspace);
        $this->assertFalse($this->validator->validate($badge, $this->user));
    }

    public function testCheckBadgeTwoRuleMatchOneLogOnWorkspace()
    {
        $workspace = new SimpleWorkspace();
        $badgeRuleA = new BadgeRule();
        $badgeRuleB = new BadgeRule();
        $restrictions = array('workspace' => $workspace);
        $this->mockFind($badgeRuleA, $restrictions, array(new Log()));
        $this->mockFind($badgeRuleB, $restrictions, array());
        $badge = new Badge();
        $badge
            ->setRules(array($badgeRuleA, $badgeRuleB))
            ->setWorkspace($workspace);
        $this->assertFalse($this->validator->validate($badge, $this->user));
    }

    public function testCheckBadgeTwoRuleMatchTwoLogOnWorkspace()
    {
        $workspace = new SimpleWorkspace();
        $badgeRuleA = new BadgeRule();
        $badgeRuleB = new BadgeRule();
        $logA = new Log();
        $logB = new Log();
        $restrictions = array('workspace' => $workspace);
        $this->mockFind($badgeRuleA, $restrictions, array($logA));
        $this->mockFind($badgeRuleB, $restrictions, array($logB));
        $badge = new Badge();
        $badge->setRules(array($badgeRuleA, $badgeRuleB))
            ->setWorkspace($workspace);
        $this->assertEquals(array($logA, $logB), $this->validator->validate($badge, $this->user));
    }

    /**
     * @dataProvider oneRuleWithMatchingLogProvider
     */
    public function testValidateOneRuleWithMatchingLog($loggedResult, $comparison)
    {
        $log = new Log();
        $log->setDetails(array('result' => $loggedResult));
        $badgeRule = new BadgeRule();
        $badgeRule->setResult('12')->setResultComparison($comparison);
        $this->mockFind($badgeRule, array(), array($log));
        $this->assertEquals(array($log), $this->validator->validateRule($badgeRule, $this->user));
    }

    public function oneRuleWithMatchingLogProvider()
    {
        return array(
            array(12, 0),
            array(13, 3),
            array(12, 4),
            array(13, 4),
            array(11, 1),
            array(12, 2),
            array(11, 2)
        );
    }

    /**
     * @dataProvider oneRuleWithNoMatchingLogProvider
     */
    public function testValidateOneRuleWithNoMatchingLog($loggedResult, $comparison)
    {
        $log = new Log();
        $log->setDetails(array('result' => $loggedResult));
        $badgeRule = new BadgeRule();
        $badgeRule->setResult('12')->setResultComparison($comparison);
        $this->mockFind($badgeRule, array(), array($log));
        $this->assertFalse($this->validator->validateRule($badgeRule, $this->user));
    }

    public function oneRuleWithNoMatchingLogProvider()
    {
        return array(
            array(11, 0),
            array(11, 3),
            array(11, 4),
            array(12, 1),
            array(13, 1),
            array(13, 2),
            array(13, 2)
        );
    }

    public function testValidateRuleOneRuleMatchOneLogOnWrongResource()
    {
        $resourceNode = new ResourceNode();
        $resourceNode->setId($resourceNodeId = rand(10, PHP_INT_MAX));
        $otherResourceNode = new ResourceNode();
        $otherResourceNode->setId($otherResourceNodeId = rand(0, 10));
        $badgeRule = new BadgeRule();
        $badgeRule->setResource($otherResourceNode);
        $this->mockFind($badgeRule, array(), array());
        $this->assertFalse($this->validator->validateRule($badgeRule, $this->user));
    }

    public function testValidateRuleOneRuleMatchOneLogOnRightResource()
    {
        $resourceNode = new ResourceNode();
        $resourceNode->setId($resourceNodeId = rand(10, PHP_INT_MAX));
        $log = new Log();
        $log->setResourceNode($resourceNode);
        $badgeRule = new BadgeRule();
        $badgeRule->setResource($resourceNode);
        $this->mockFind($badgeRule, array(), array($log));
        $this->assertEquals(array($log), $this->validator->validateRule($badgeRule, $this->user));
    }

    private function mockFind(BadgeRule $rule, array $restrictions, $returnValue)
    {
        $this->logRepo->shouldReceive('findByRuleAndUser')
            ->with($rule, $this->user, $restrictions)
            ->andReturn($returnValue);
    }
}
