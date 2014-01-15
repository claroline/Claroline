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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class ValidatorTest extends MockeryTestCase
{
    const CONSTRAINT_ACTION_WITH = 'l.action = :action';
    const CONSTRAINT_ACTION_KEY  = 'action';

    const CONSTRAINT_DOER_WITH   = 'l.doer = :doer';
    const CONSTRAINT_DOER_KEY    = 'doer';

    const CONSTRAINT_RECEIVER_WITH   = 'l.receiver = :receiver';
    const CONSTRAINT_RECEIVER_KEY    = 'receiver';

    protected $logRepository;
    protected $queryBuilder;
    protected $query;

    protected function setUp()
    {
        $this->logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $this->queryBuilder  = $this->mock('Doctrine\ORM\QueryBuilder');
        $this->query         = $this->mock('Doctrine\ORM\AbstractQuery');
    }

    protected function getLogRepository($queryBuilder)
    {
        $logRepository = $this->logRepository;
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        return $logRepository;
    }

    protected function getQueryBuilderForDoerAndActionConstraint($action, $user, $query)
    {
        $queryBuilder = $this->queryBuilder;
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        return $queryBuilder;
    }

    protected function getQueryBuilderForReceiverAndActionConstraint($action, $user, $query)
    {
        $queryBuilder = $this->queryBuilder;
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_RECEIVER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_RECEIVER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        return $queryBuilder;
    }

    protected function getQueryBuilderForDoerActionAndOccurenceConstraint($action, $user, $rule, $query)
    {
        $queryBuilder = $this->queryBuilder;
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('setMaxResults')->once()->with($rule->getOccurrence())->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        return $queryBuilder;
    }

    protected function getQueryBuilderForDoerTwoActionAndOccurenceConstraint($action, $action2, $user, $rule, $query)
    {
        $queryBuilder = $this->queryBuilder;
        $queryBuilder
            ->shouldReceive('andWhere')->twice()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->twice()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action2)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->twice()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->twice()->andReturn($query);

        return $queryBuilder;
    }

    public function testValidateRuleDoerActionMatchNoLog()
    {
        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0);

        $this->query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionMatchLog()
    {
        $log    = new Log();
        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0);

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }
    public function testValidateRuleDoerActionBadgeMatchNoLog()
    {
        $user   = new User();
        $action = uniqid();
        $badge  = new Badge();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setBadge($badge);

        $this->query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionBadgeMatchLog()
    {
        $user   = new User();
        $action = uniqid();
        $badge  = new Badge();
        $badge->setId(rand(0, PHP_INT_MAX));

        $log = new Log();
        $log->setDetails(array(
            'badge' => array(
                'id' => $badge->getId()
            )
        ));

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setBadge($badge);

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionBadgeMatchNoLogWrongBadge()
    {
        $user   = new User();
        $action = uniqid();
        $badge  = new Badge();
        $badge->setId(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX));

        $log = new Log();
        $log->setDetails(array(
            'badge' => array(
                'id' => rand(0, PHP_INT_MAX / 2)
            )
        ));

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setBadge($badge);

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleReceiverActionMatchNoLog()
    {
        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(1);

        $this->query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder  = $this->getQueryBuilderForReceiverAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleReceiverActionMatchLog()
    {
        $log    = new Log();
        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(1);

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForReceiverAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerOccurenceActionMatchNoLog()
    {
        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setOccurrence($occurence = rand(1, PHP_INT_MAX));

        $this->query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder  = $this->getQueryBuilderForDoerActionAndOccurenceConstraint($action, $user, $rule, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerOccurenceActionMatchLog()
    {
        $log    = new Log();
        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setOccurrence($occurence = rand(1, 5));

        $associatedLogs = array_fill(1, $rule->getOccurrence(), $log);

        $this->query->shouldReceive('getResult')->once()->andReturn($associatedLogs);

        $queryBuilder  = $this->getQueryBuilderForDoerActionAndOccurenceConstraint($action, $user, $rule, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals($associatedLogs, $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerOccurenceActionMatchWrongNumberOfLog()
    {
        $log    = new Log();
        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setOccurrence($occurence = rand(2, 5));

        $associatedLogs = array_fill(1, $rule->getOccurrence() - 1, $log);

        $this->query->shouldReceive('getResult')->once()->andReturn($associatedLogs);

        $queryBuilder  = $this->getQueryBuilderForDoerActionAndOccurenceConstraint($action, $user, $rule, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultEqualMatchNoLog()
    {
        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(rand(0, PHP_INT_MAX))
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_EQUAL));

        $this->query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultNotEqualMatchNoLog()
    {
        $result = rand(0, PHP_INT_MAX);
        $log    = new Log();
        $log->setDetails(array('result' => $result));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult($result - 1)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_EQUAL));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultEqualMatchLog()
    {
        $result = rand(0, PHP_INT_MAX);
        $log    = new Log();
        $log->setDetails(array('result' => $result));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult($result)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_EQUAL));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorMatchLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 12));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(9)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorButInferiorMatchNoLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 12));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(42)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorButEqualMatchNoLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 12));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorEqualButSuperiorMatchLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 12));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(9)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorEqualButEqualMatchLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 12));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorEqualButInferiorMatchLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 9));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorMatchLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 9));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorButSuperiorMatchNoLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 42));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorButEqualMatchNoLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 12));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorEqualButInferiorMatchLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 9));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR_EQUAL));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorEqualButEqualMatchLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 12));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorEqualButSuperiorMatchLog()
    {
        $log = new Log();
        $log->setDetails(array('result' => 12));

        $user   = new User();
        $action = uniqid();
        $rule   = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(9)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR_EQUAL));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateWithNoRule()
    {
        $badge         = new Badge();
        $user          = new User();
        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testValidateWithTowRuleDoerActionMatchNoLog()
    {
        $badge   = new Badge();
        $user    = new User();
        $action  = uniqid();
        $action2 = uniqid();

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUserType(0);

        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action2)
            ->setUserType(0);

        $badge->setRules(array($rule, $rule2));

        $this->query->shouldReceive('getResult')->twice()->andReturn(array());

        $queryBuilder  = $this->getQueryBuilderForDoerTwoActionAndOccurenceConstraint($action, $action2, $user, $rule, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testValidateWithTowRuleDoerActionMatchLogJustForTheFirstRule()
    {
        $badge   = new Badge();
        $user    = new User();
        $action  = uniqid();
        $action2 = uniqid();
        $log     = new Log();

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUserType(0);

        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action2)
            ->setUserType(0);

        $badge->setRules(array($rule, $rule2));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));
        $this->query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder  = $this->getQueryBuilderForDoerTwoActionAndOccurenceConstraint($action, $action2, $user, $rule, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testValidateWithTowRuleDoerActionMatchLogJustForTheSecondRule()
    {
        $badge   = new Badge();
        $user    = new User();
        $action  = uniqid();
        $action2 = uniqid();
        $log     = new Log();

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUserType(0);

        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action2)
            ->setUserType(0);

        $badge->setRules(array($rule, $rule2));

        $this->query->shouldReceive('getResult')->once()->andReturn(array());
        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerTwoActionAndOccurenceConstraint($action, $action2, $user, $rule, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testValidateWithTowRuleDoerActionMatchLogForBothRule()
    {
        $badge   = new Badge();
        $user    = new User();
        $action  = uniqid();
        $action2 = uniqid();
        $log     = new Log();

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUserType(0);

        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action2)
            ->setUserType(0);

        $badge->setRules(array($rule, $rule2));

        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));
        $this->query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder  = $this->getQueryBuilderForDoerTwoActionAndOccurenceConstraint($action, $action2, $user, $rule, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log, $log), $ruleValidator->validate($badge, $user));
    }
}
