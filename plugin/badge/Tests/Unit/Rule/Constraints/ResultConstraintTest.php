<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\BadgeBundle\Rule\Constraints;

use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Rule\Constraints\ResultConstraint;
use Icap\BadgeBundle\Entity\BadgeRule;

class ResultConstraintTest extends MockeryTestCase
{
    public function testIsNotApplicableTo()
    {
        $badgeRule = new BadgeRule();
        $resultConstraint = new ResultConstraint();

        $this->assertFalse($resultConstraint->isApplicableTo($badgeRule));
    }

    public function testIsApplicableTo()
    {
        $badgeRule = new BadgeRule();
        $badgeRule->setResult(rand(0, PHP_INT_MAX));

        $resultConstraint = new ResultConstraint();

        $this->assertTrue($resultConstraint->isApplicableTo($badgeRule));
    }

    public function testValidateNoLog()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setOccurrence(1);

        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs([]);

        $this->assertFalse($resultConstraint->validate());
    }

    public function testValidateNoLogNoResultInLogDetails()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setOccurrence(1)
            ->setResult(rand(13, PHP_INT_MAX))
            ->setResultComparison(0);

        $log = new Log();
        $log->setDetails([]);

        $associatedLogs = [$log];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertFalse($resultConstraint->validate());
    }

    public function testValidateOneWrongLogResultEqual()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setOccurrence(1)
            ->setResult(rand(13, PHP_INT_MAX))
            ->setResultComparison(0);

        $log = new Log();
        $log->setDetails(['result' => 12]);

        $associatedLogs = [$log];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertFalse($resultConstraint->validate());
    }

    public function testValidateOneRightLogResultEqual()
    {
        $result = rand(0, PHP_INT_MAX);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult($result)
            ->setResultComparison(0);

        $log = new Log();
        $log->setDetails(['result' => $result]);

        $associatedLogs = [$log];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightLogResultEqual()
    {
        $result = rand(0, PHP_INT_MAX);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult($result)
            ->setResultComparison(0);

        $log = new Log();
        $log->setDetails(['result' => $result]);

        $log2 = new Log();
        $log2->setDetails(['result' => $result]);

        $associatedLogs = [$log, $log2];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateOneRightLogResultInferior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX))
            ->setResultComparison(1);

        $log = new Log();
        $log->setDetails(['result' => rand(0, PHP_INT_MAX / 2)]);

        $associatedLogs = [$log];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightLogResultInferior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX))
            ->setResultComparison(1);

        $log = new Log();
        $log->setDetails(['result' => rand(0, PHP_INT_MAX / 2)]);

        $log2 = new Log();
        $log2->setDetails(['result' => rand(0, PHP_INT_MAX / 2)]);

        $associatedLogs = [$log, $log2];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateThreeRightLogResultInferior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX))
            ->setResultComparison(1);

        $log = new Log();
        $log->setDetails(['result' => rand(0, PHP_INT_MAX / 2)]);

        $log2 = new Log();
        $log2->setDetails(['result' => rand(0, PHP_INT_MAX / 2)]);

        $log3 = new Log();
        $log3->setDetails(['result' => rand(0, PHP_INT_MAX / 2)]);

        $associatedLogs = [$log, $log2, $log3];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightOneWrongLogLogResultInferior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setOccurrence(1)
            ->setResult(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX - PHP_INT_MAX / 4))
            ->setResultComparison(1);

        $log = new Log();
        $log->setDetails(['result' => rand(0, PHP_INT_MAX / 2)]);

        $log2 = new Log();
        $log2->setDetails(['result' => rand(PHP_INT_MAX - PHP_INT_MAX / 4 + 1, PHP_INT_MAX)]);

        $log3 = new Log();
        $log3->setDetails(['result' => rand(0, PHP_INT_MAX / 2)]);

        $associatedLogs = [$log, $log2, $log3];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateOneInferiorRightLogResultInferiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult($result)
            ->setResultComparison(2);

        $log = new Log();
        $log->setDetails(['result' => $result]);

        $associatedLogs = [$log];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateOneEqualRightLogResultInferiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult($result)
            ->setResultComparison(2);

        $log = new Log();
        $log->setDetails(['result' => $result]);

        $associatedLogs = [$log];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightLogResultInferiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult($result)
            ->setResultComparison(2);

        $log = new Log();
        $log->setDetails(['result' => $result]);

        $log2 = new Log();
        $log2->setDetails(['result' => $result]);

        $associatedLogs = [$log, $log2];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateThreeRightLogResultInferiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult($result)
            ->setResultComparison(2);

        $log = new Log();
        $log->setDetails(['result' => $result]);

        $log2 = new Log();
        $log2->setDetails(['result' => $result]);

        $log3 = new Log();
        $log3->setDetails(['result' => $result]);

        $associatedLogs = [$log, $log2, $log3];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightOneWrongLogLogResultInferiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setOccurrence(1)
            ->setResult($result)
            ->setResultComparison(2);

        $log = new Log();
        $log->setDetails(['result' => $result]);

        $log2 = new Log();
        $log2->setDetails(['result' => $result + 1]);

        $log3 = new Log();
        $log3->setDetails(['result' => $result]);

        $associatedLogs = [$log, $log2, $log3];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateOneRightLogResultSuperior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(0, PHP_INT_MAX / 2))
            ->setResultComparison(3);

        $log = new Log();
        $log->setDetails(['result' => rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)]);

        $associatedLogs = [$log];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightLogResultSuperior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(0, PHP_INT_MAX / 2))
            ->setResultComparison(3);

        $log = new Log();
        $log->setDetails(['result' => rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)]);

        $log2 = new Log();
        $log2->setDetails(['result' => rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)]);

        $associatedLogs = [$log, $log2];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateThreeRightLogResultSuperior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult(rand(0, PHP_INT_MAX / 2))
            ->setResultComparison(3);

        $log = new Log();
        $log->setDetails(['result' => rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)]);

        $log2 = new Log();
        $log2->setDetails(['result' => rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)]);

        $log3 = new Log();
        $log3->setDetails(['result' => rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)]);

        $associatedLogs = [$log, $log2, $log3];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightOneWrongLogLogResultSuperior()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setOccurrence(1)
            ->setResult(rand(PHP_INT_MAX / 4, PHP_INT_MAX / 2))
            ->setResultComparison(3);

        $log = new Log();
        $log->setDetails(['result' => rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)]);

        $log2 = new Log();
        $log2->setDetails(['result' => rand(0, PHP_INT_MAX / 4)]);

        $log3 = new Log();
        $log3->setDetails(['result' => rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX)]);

        $associatedLogs = [$log, $log2, $log3];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateOneSuperiorRightLogResultSuperiorEqual()
    {
        $result = rand(0, PHP_INT_MAX / 2);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult($result)
            ->setResultComparison(4);

        $log = new Log();
        $log->setDetails(['result' => $result + rand(0, PHP_INT_MAX / 2)]);

        $associatedLogs = [$log];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateOneEqualRightLogResultSuperiorEqual()
    {
        $result = rand(0, PHP_INT_MAX);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult($result)
            ->setResultComparison(4);

        $log = new Log();
        $log->setDetails(['result' => $result]);

        $associatedLogs = [$log];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightLogResultSuperiorEqual()
    {
        $result = rand(0, PHP_INT_MAX / 2);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult($result)
            ->setResultComparison(4);

        $log = new Log();
        $log->setDetails(['result' => $result + rand(0, PHP_INT_MAX / 2)]);

        $log2 = new Log();
        $log2->setDetails(['result' => $result + rand(0, PHP_INT_MAX / 2)]);

        $associatedLogs = [$log, $log2];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateThreeRightLogResultSuperiorEqual()
    {
        $result = rand(0, PHP_INT_MAX / 2);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setResult($result)
            ->setResultComparison(4);

        $log = new Log();
        $log->setDetails(['result' => $result + rand(0, PHP_INT_MAX / 2)]);

        $log2 = new Log();
        $log2->setDetails(['result' => $result + rand(0, PHP_INT_MAX / 2)]);

        $log3 = new Log();
        $log3->setDetails(['result' => $result + rand(0, PHP_INT_MAX / 2)]);

        $associatedLogs = [$log, $log2, $log3];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }

    public function testValidateTwoRightOneWrongLogLogResultSuperiorEqual()
    {
        $result = rand(0, PHP_INT_MAX / 2);

        $badgeRule = new BadgeRule();
        $badgeRule
            ->setOccurrence(1)
            ->setResult($result)
            ->setResultComparison(4);

        $log = new Log();
        $log->setDetails(['result' => $result + rand(0, PHP_INT_MAX / 2)]);

        $log2 = new Log();
        $log2->setDetails(['result' => $result - 1]);

        $log3 = new Log();
        $log3->setDetails(['result' => $result + rand(0, PHP_INT_MAX / 2)]);

        $associatedLogs = [$log, $log2, $log3];
        $resultConstraint = new ResultConstraint();
        $resultConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($resultConstraint->validate());
    }
}
