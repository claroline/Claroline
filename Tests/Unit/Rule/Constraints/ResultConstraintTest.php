<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Rule\Constraints;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

class ResultConstraintTest extends MockeryTestCase
{
    public function testValidateNoLog()
    {
        $resultConstraint = $this->createResultConstraint(new BadgeRule(), array());
        $this->assertFalse($resultConstraint->validate());
    }

    public function testValidateOneWrongLogResultEqual()
    {
        $badgeRule = new BadgeRule();
        $badgeRule->setResult(rand(13, PHP_INT_MAX))->setResultComparison(0);
        $resultConstraint = $this->createResultConstraint($badgeRule, array(12));
        $this->assertFalse($resultConstraint->validate());
    }

    public function testValidateOneRightLogResultEqual()
    {
        $result = rand(0, PHP_INT_MAX);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(0);
        $resultConstraint = $this->createResultConstraint($badgeRule, array($result));
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightLogResultEqual()
    {
        $result = rand(0, PHP_INT_MAX);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(0);
        $resultConstraint = $this->createResultConstraint($badgeRule, array($result, $result));
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateOneRightLogResultInferior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX))
            ->setResultComparison(1);
        $resultConstraint = $this->createResultConstraint($badgeRule, array(rand(0, PHP_INT_MAX / 2)));
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightLogResultInferior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX))
            ->setResultComparison(1);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array(rand(0, PHP_INT_MAX / 2), rand(0, PHP_INT_MAX / 2))
        );
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateThreeRightLogResultInferior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX))
            ->setResultComparison(1);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array(
                rand(0, PHP_INT_MAX / 2),
                rand(0, PHP_INT_MAX / 2),
                rand(0, PHP_INT_MAX / 2)
            )
        );
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightOneWrongLogLogResultInferior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX - PHP_INT_MAX / 4))
            ->setResultComparison(1);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array(
                rand(0, PHP_INT_MAX / 2),
                rand(PHP_INT_MAX - PHP_INT_MAX / 4 + 1, PHP_INT_MAX),
                rand(0, PHP_INT_MAX / 2)
            )
        );
        $this->assertFalse($resultConstraint->validate());
    }

    public function testValidateOneInferiorRightLogResultInferiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(2);
        $resultConstraint = $this->createResultConstraint($badgeRule, array($result));
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateOneEqualRightLogResultInferiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(2);
        $resultConstraint = $this->createResultConstraint($badgeRule, array($result));
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightLogResultInferiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(2);
        $resultConstraint = $this->createResultConstraint($badgeRule, array($result, $result));
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateThreeRightLogResultInferiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(2);
        $resultConstraint = $this->createResultConstraint($badgeRule, array($result, $result, $result));
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightOneWrongLogLogResultInferiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(2);
        $resultConstraint = $this->createResultConstraint($badgeRule, array($result, $result + 1, $result));
        $this->assertFalse($resultConstraint->validate());
    }

    public function testValidateOneRightLogResultSuperior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(0, PHP_INT_MAX / 2))
            ->setResultComparison(3);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX))
        );
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightLogResultSuperior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(0, PHP_INT_MAX / 2))
            ->setResultComparison(3);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array(
                rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX),
                rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)
            )
        );
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateThreeRightLogResultSuperior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(0, PHP_INT_MAX / 2))
            ->setResultComparison(3);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array(
                rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX),
                rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX),
                rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)
            )
        );
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightOneWrongLogLogResultSuperior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(PHP_INT_MAX / 4, PHP_INT_MAX / 2))
            ->setResultComparison(3);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array(
                rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX),
                rand(0, PHP_INT_MAX / 4),
                rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)
            )
        );
        $this->assertFalse($resultConstraint->validate());
    }

    public function testValidateOneSuperiorRightLogResultSuperiorEqual()
    {
        $result = rand(0, PHP_INT_MAX / 2);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(4);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array($result + rand(0, PHP_INT_MAX / 2))
        );
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateOneEqualRightLogResultSuperiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(4);
        $resultConstraint = $this->createResultConstraint($badgeRule, array($result));
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightLogResultSuperiorEqual()
    {
        $result = rand(0, PHP_INT_MAX / 2);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(4);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array(
                $result + rand(0, PHP_INT_MAX / 2),
                $result + rand(0, PHP_INT_MAX / 2)
            )
        );
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateThreeRightLogResultSuperiorEqual()
    {
        $result = rand(0, PHP_INT_MAX / 2);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(4);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array(
                $result + rand(0, PHP_INT_MAX / 2),
                $result + rand(0, PHP_INT_MAX / 2),
                $result + rand(0, PHP_INT_MAX / 2)
            )
        );
        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightOneWrongLogLogResultSuperiorEqual()
    {
        $result = rand(0, PHP_INT_MAX / 2);
        $badgeRule = new BadgeRule();
        $badgeRule->setResult($result)->setResultComparison(4);
        $resultConstraint = $this->createResultConstraint(
            $badgeRule,
            array(
                $result + rand(0, PHP_INT_MAX / 2),
                $result - 1,
                $result + rand(0, PHP_INT_MAX / 2)
            )
        );
        $this->assertFalse($resultConstraint->validate());
    }

    private function createResultConstraint(BadgeRule $badgeRule, array $results)
    {
        $logs = array();

        foreach ($results as $result) {
            $log = new Log();
            $log->setDetails(array('result' => $result));
            $logs[] = $log;
        }

        return new ResultConstraint($badgeRule, $logs);
    }
}
