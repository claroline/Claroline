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
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use \Mockery as m;
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

    const CONSTRAINT_BADGE_WITH   = 'l.badge = :badge';
    const CONSTRAINT_BADGE_KEY    = 'badge';

    public function testValidateRuleDoerActionMatchNoLog()
    {
        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0);

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionMatchLog()
    {
        $log       = new Log();
        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0);

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }
    public function testValidateRuleDoerActionBadgeMatchNoLog()
    {
        $user      = new User();
        $action    = uniqid();
        $badge     = new Badge();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setBadge($badge);

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_BADGE_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_BADGE_KEY, $badge)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionBadgeMatchLog()
    {
        $log       = new Log();
        $user      = new User();
        $action    = uniqid();
        $badge     = new Badge();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setBadge($badge);

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_BADGE_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_BADGE_KEY, $badge)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleReceiverActionMatchNoLog()
    {
        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(1);

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_RECEIVER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_RECEIVER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleReceiverActionMatchLog()
    {
        $log       = new Log();
        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(1);

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_RECEIVER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_RECEIVER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerOccurenceActionMatchNoLog()
    {
        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setOccurrence($occurence = rand(1, PHP_INT_MAX));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('setMaxResults')->once()->with($rule->getOccurrence())->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerOccurenceActionMatchLog()
    {
        $log       = new Log();
        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setOccurrence($occurence = rand(1, 5));

        $associatedLogs = array_fill(1, $rule->getOccurrence(), $log);

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn($associatedLogs);

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('setMaxResults')->once()->with($rule->getOccurrence())->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals($associatedLogs, $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerOccurenceActionMatchWrongNumberOfLog()
    {
        $log       = new Log();
        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setOccurrence($occurence = rand(2, 5));

        $associatedLogs = array_fill(1, $rule->getOccurrence() - 1, $log);

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn($associatedLogs);

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('setMaxResults')->once()->with($rule->getOccurrence())->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultEqualMatchNoLog()
    {
        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(rand(0, PHP_INT_MAX))
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_EQUAL));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultNotEqualMatchNoLog()
    {
        $result = rand(0, PHP_INT_MAX);
        $log    = new Log();
        $log->setDetails(array('result' => $result));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult($result - 1)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_EQUAL));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultEqualMatchLog()
    {
        $result = rand(0, PHP_INT_MAX);
        $log    = new Log();
        $log->setDetails(array('result' => $result));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult($result)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_EQUAL));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorMatchLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 12));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(9)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorButInferiorMatchNoLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 12));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(42)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorButEqualMatchNoLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 12));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorEqualButSuperiorMatchLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 12));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(9)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorEqualButEqualMatchLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 12));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultSuperiorEqualButInferiorMatchLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 9));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorMatchLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 9));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorButSuperiorMatchNoLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 42));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorButEqualMatchNoLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 12));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }
        public function testValidateRuleDoerActionResultInferiorEqualButInferiorMatchLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 9));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR_EQUAL));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorEqualButEqualMatchLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 12));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log), $ruleValidator->validateRule($rule));
    }

    public function testValidateRuleDoerActionResultInferiorEqualButSuperiorMatchLog()
    {
        $log    = new Log();
        $log->setDetails(array('result' => 12));

        $user      = new User();
        $action    = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(9)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR_EQUAL));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->once()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->once()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validateRule($rule));
    }

    public function testValidateWithTowRuleDoerActionMatchNoLog()
    {
        $badge     = new Badge();
        $user      = new User();
        $action    = uniqid();
        $action2   = uniqid();

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUserType(0);

        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action2)
            ->setUserType(0);

        $badge->setRules(array($rule, $rule2));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->twice()->andReturn(array());

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->twice()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->twice()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action2)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->twice()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->twice()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testValidateWithTowRuleDoerActionMatchLogJustForTheFirstRule()
    {
        $badge     = new Badge();
        $user      = new User();
        $action    = uniqid();
        $action2   = uniqid();
        $log       = new Log();

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUserType(0);

        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action2)
            ->setUserType(0);

        $badge->setRules(array($rule, $rule2));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));
        $query->shouldReceive('getResult')->once()->andReturn(array());

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->twice()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->twice()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action2)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->twice()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->twice()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testValidateWithTowRuleDoerActionMatchLogJustForTheSecondRule()
    {
        $badge     = new Badge();
        $user      = new User();
        $action    = uniqid();
        $action2   = uniqid();
        $log       = new Log();

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUserType(0);

        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action2)
            ->setUserType(0);

        $badge->setRules(array($rule, $rule2));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array());
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->twice()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->twice()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action2)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->twice()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->twice()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertFalse($ruleValidator->validate($badge, $user));
    }

    public function testValidateWithTowRuleDoerActionMatchLogForBothRule()
    {
        $badge     = new Badge();
        $user      = new User();
        $action    = uniqid();
        $action2   = uniqid();
        $log       = new Log();

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUserType(0);

        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action2)
            ->setUserType(0);

        $badge->setRules(array($rule, $rule2));

        $query = $this->mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getResult')->once()->andReturn(array($log));
        $query->shouldReceive('getResult')->once()->andReturn(array($log));

        $queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $queryBuilder
            ->shouldReceive('andWhere')->twice()->with(self::CONSTRAINT_ACTION_WITH)->andReturn($queryBuilder)
            ->shouldReceive('andWhere')->twice()->with(self::CONSTRAINT_DOER_WITH)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->once()->with(self::CONSTRAINT_ACTION_KEY, $action2)->andReturn($queryBuilder)
            ->shouldReceive('setParameter')->twice()->with(self::CONSTRAINT_DOER_KEY, $user)->andReturn($queryBuilder)
            ->shouldReceive('getQuery')->twice()->andReturn($query);

        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $logRepository->shouldReceive('defaultQueryBuilderForBadge')->once()->andReturn($queryBuilder);

        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(array($log, $log), $ruleValidator->validate($badge, $user));
    }
}
