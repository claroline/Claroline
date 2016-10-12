<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\BadgeBundle\Rule;

use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Rule\Entity\Rule;
use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Entity\BadgeRule;

class ValidatorTest extends MockeryTestCase
{
    const CONSTRAINT_ACTION_WITH = 'l.action = :action';
    const CONSTRAINT_ACTION_KEY = 'action';

    const CONSTRAINT_DOER_WITH = 'l.doer = :doer';
    const CONSTRAINT_DOER_KEY = 'doer';

    const CONSTRAINT_RECEIVER_WITH = 'l.receiver = :receiver';
    const CONSTRAINT_RECEIVER_KEY = 'receiver';

    protected $logRepository;
    protected $queryBuilder;
    protected $query;

    protected function setUp()
    {
        $this->logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $this->queryBuilder = $this->mock('Doctrine\ORM\QueryBuilder');
        $this->query = $this->mock('Doctrine\ORM\AbstractQuery');
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

    protected function getQueryBuilderForDoerTwoActionAndOccurenceConstraint($action, $action2, $user, $query)
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

    /**
     * @dataProvider doerProvider
     */
    public function testValidateRuleDoer($user, $action, $rule, $result, $validateRule)
    {
        $query = $this->query;
        $query->shouldReceive('getResult')->once()->andReturn($result);

        $queryBuilder = $this->getQueryBuilderForDoerAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals($validateRule, $ruleValidator->validateRule($rule));
    }

    public function doerProvider()
    {
        $log = new Log();
        $action = uniqid();
        $user = new User();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0);

        $badge = new Badge();
        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setBadge($badge);
        $log2 = new Log();
        $log2->setDetails([
            'badge' => [
                'id' => $badge->getId(),
            ],
        ]);

        $badge2 = new Badge();
        $badge2->setId(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX));
        $rule3 = new BadgeRule();
        $rule3
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setBadge($badge2);
        $log3 = new Log();
        $log3->setDetails([
            'badge' => [
                'id' => rand(0, PHP_INT_MAX / 2),
            ],
        ]);

        $rule4 = new BadgeRule();
        $rule4
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(rand(0, PHP_INT_MAX))
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_EQUAL));

        $result = rand(0, PHP_INT_MAX);
        $log4 = new Log();
        $log4->setDetails(['result' => $result]);
        $rule5 = new BadgeRule();
        $rule5
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult($result - 1)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_EQUAL));

        $rule6 = new BadgeRule();
        $rule6
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult($result)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_EQUAL));

        $log5 = new Log();
        $log5->setDetails(['result' => 12]);
        $rule7 = new BadgeRule();
        $rule7
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(9)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR));

        $rule8 = new BadgeRule();
        $rule8
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(42)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR));

        $rule9 = new BadgeRule();
        $rule9
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR));

        $rule10 = new BadgeRule();
        $rule10
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(9)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $rule11 = new BadgeRule();
        $rule11
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $log6 = new Log();
        $log6->setDetails(['result' => 9]);
        $rule12 = new BadgeRule();
        $rule12
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $rule13 = new BadgeRule();
        $rule13
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR));

        $log7 = new Log();
        $log7->setDetails(['result' => 42]);

        $rule14 = new BadgeRule();
        $rule14
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR_EQUAL));

        $rule15 = new BadgeRule();
        $rule15
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(12)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_SUPERIOR_EQUAL));

        $rule16 = new BadgeRule();
        $rule16
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setResult(9)
            ->setResultComparison(Rule::getResultComparisonTypeValue(Rule::RESULT_INFERIOR_EQUAL));

        return [
            [$user, $action, $rule,   [],      false],        //testValidateRuleDoerActionMatchNoLog
            [$user, $action, $rule,   [$log],  [$log]],  //testValidateRuleDoerActionMatchLog
            [$user, $action, $rule2,  [$log2], [$log2]], //testValidateRuleDoerActionBadgeMatchLog
            [$user, $action, $rule2,  [],      false],        //testValidateRuleDoerActionBadgeMatchNoLog
            [$user, $action, $rule3,  [$log3], false],        //testValidateRuleDoerActionBadgeMatchNoLogWrongBadge
            [$user, $action, $rule4,  [],      false],        //testValidateRuleDoerActionResultEqualMatchNoLog
            [$user, $action, $rule5,  [$log4], false],        //testValidateRuleDoerActionResultNotEqualMatchNoLog
            [$user, $action, $rule6,  [$log4], [$log4]], //testValidateRuleDoerActionResultEqualMatchLog
            [$user, $action, $rule7,  [$log5], [$log5]], //testValidateRuleDoerActionResultSuperiorMatchLog
            [$user, $action, $rule8,  [$log5], false],        //testValidateRuleDoerActionResultSuperiorButInferiorMatchNoLog
            [$user, $action, $rule9,  [$log5], false],        //testValidateRuleDoerActionResultSuperiorButEqualMatchNoLog
            [$user, $action, $rule10, [$log5], [$log5]], //testValidateRuleDoerActionResultSuperiorEqualButSuperiorMatchLog
            [$user, $action, $rule11, [$log5], [$log5]], //testValidateRuleDoerActionResultSuperiorEqualButEqualMatchLog
            [$user, $action, $rule12, [$log6], false],        //testValidateRuleDoerActionResultSuperiorEqualButInferiorMatchLog
            [$user, $action, $rule13, [$log6], [$log6]], //testValidateRuleDoerActionResultInferiorMatchLog
            [$user, $action, $rule13, [$log7], false],        //testValidateRuleDoerActionResultInferiorButSuperiorMatchNoLog
            [$user, $action, $rule13, [$log5], false],        //testValidateRuleDoerActionResultInferiorButEqualMatchNoLog
            [$user, $action, $rule14, [$log6], [$log6]], //testValidateRuleDoerActionResultInferiorEqualButInferiorMatchLog
            [$user, $action, $rule15, [$log5], [$log5]], //testValidateRuleDoerActionResultInferiorEqualButEqualMatchLog
            [$user, $action, $rule16, [$log5], false],  //testValidateRuleDoerActionResultInferiorEqualButSuperiorMatchLog
        ];
    }

    /**
     * @dataProvider receiverProvider
     */
    public function testValidateRuleReceiver($user, $action, $rule, $result, $validateRule)
    {
        $query = $this->query;
        $query->shouldReceive('getResult')->once()->andReturn($result);

        $queryBuilder = $this->getQueryBuilderForReceiverAndActionConstraint($action, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals($validateRule, ($ruleValidator->validateRule($rule)));
    }

    public function receiverProvider()
    {
        $log = new Log();
        $user = new User();
        $action = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(1);

        return [
            [$user, $action, $rule, [],     false],          //testValidateRuleReceiverActionMatchNoLog
            [$user, $action, $rule, [$log], [$log]],    //testValidateRuleReceiverActionMatchLog
        ];
    }

    /**
     * @dataProvider receiverWithOccurenceProvider
     */
    public function testValidateRuleReceiverWithOccurence($user, $action, $rule, $result, $validateRule)
    {
        $query = $this->query;
        $query->shouldReceive('getResult')->once()->andReturn($result);

        $queryBuilder = $this->getQueryBuilderForDoerActionAndOccurenceConstraint($action, $user, $rule, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals($validateRule, $ruleValidator->validateRule($rule));
    }

    public function receiverWithOccurenceProvider()
    {
        $log = new Log();
        $user = new User();
        $action = uniqid();
        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setOccurrence($occurence = rand(1, PHP_INT_MAX));

        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setOccurrence($occurence = rand(1, 5));
        $associatedLogs = array_fill(1, $rule2->getOccurrence(), $log);

        $rule3 = new BadgeRule();
        $rule3
            ->setAction($action)
            ->setUser($user)
            ->setUserType(0)
            ->setOccurrence($occurence = rand(2, 5));
        $associatedLogs2 = array_fill(1, $rule3->getOccurrence() - 1, $log);

        return [
            [$user, $action, $rule, [], false],                    //testValidateRuleDoerOccurenceActionMatchNoLog
            [$user, $action, $rule2, $associatedLogs, $associatedLogs], //testValidateRuleDoerOccurenceActionMatchLog
            [$user, $action, $rule3, $associatedLogs2, false],          //testValidateRuleDoerOccurenceActionMatchWrongNumberOfLog
        ];
    }

    public function testValidateWithNoRule()
    {
        $badge = new Badge();
        $user = new User();
        $logRepository = $this->mock('Claroline\CoreBundle\Repository\Log\LogRepository');
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals(['validRules' => 0, 'rules' => []], $ruleValidator->validate($badge, $user));
    }

    /**
     * @dataProvider validateWithTwoRuleProvider
     */
    public function testValidateWithTowRule($badge, $user, $action, $action2, $result, $result2, $validateRule)
    {
        $query = $this->query;

        $query->shouldReceive('getResult')->once()->andReturn($result);
        $query->shouldReceive('getResult')->once()->andReturn($result2);

        $queryBuilder = $this->getQueryBuilderForDoerTwoActionAndOccurenceConstraint($action, $action2, $user, $this->query);
        $logRepository = $this->getLogRepository($queryBuilder);
        $ruleValidator = new Validator($logRepository);

        $this->assertEquals($validateRule, $ruleValidator->validate($badge, $user));
    }

    public function validateWithTwoRuleProvider()
    {
        $badge = new Badge();
        $user = new User();
        $action = uniqid();
        $action2 = uniqid();
        $log = new Log();

        $rule = new BadgeRule();
        $rule
            ->setAction($action)
            ->setUserType(0);

        $rule2 = new BadgeRule();
        $rule2
            ->setAction($action2)
            ->setUserType(0);

        $badge->setRules([$rule, $rule2]);

        $validateRule = ['validRules' => 0, 'rules' => []];
        $validateRule2 = ['validRules' => 1, 'rules' => [['rule' => $rule, 'logs' => [$log]]]];
        $validateRule3 = ['validRules' => 1, 'rules' => [['rule' => $rule2, 'logs' => [$log]]]];
        $validateRule4 = ['validRules' => 2, 'rules' => [['rule' => $rule,  'logs' => [$log]], ['rule' => $rule2, 'logs' => [$log]]]];

        return [
            [$badge, $user, $action, $action2, [],     [],     $validateRule],  //testValidateWithTowRuleDoerActionMatchNoLog
            [$badge, $user, $action, $action2, [$log], [],     $validateRule2], //testValidateWithTowRuleDoerActionMatchLogJustForTheFirstRule
            [$badge, $user, $action, $action2, [],     [$log], $validateRule3], //testValidateWithTowRuleDoerActionMatchLogJustForTheSecondRule
            [$badge, $user, $action, $action2, [$log], [$log], $validateRule4], //testValidateWithTowRuleDoerActionMatchLogForBothRule
        ];
    }
}
