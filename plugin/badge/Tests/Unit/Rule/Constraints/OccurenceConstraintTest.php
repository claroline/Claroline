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
use Claroline\CoreBundle\Rule\Constraints\OccurenceConstraint;
use Claroline\CoreBundle\Rule\Constraints\ResultConstraint;
use Icap\BadgeBundle\Entity\BadgeRule;

class OccurenceConstraintTest extends MockeryTestCase
{
    public function testIsNotApplicableTo()
    {
        $badgeRule = new BadgeRule();
        $occurenceConstraint = new ResultConstraint();

        $this->assertFalse($occurenceConstraint->isApplicableTo($badgeRule));
    }

    public function testIsApplicableTo()
    {
        $badgeRule = new BadgeRule();
        $badgeRule->setOccurrence(rand(0, PHP_INT_MAX));

        $occurenceConstraint = new ResultConstraint();

        $this->assertFalse($occurenceConstraint->isApplicableTo($badgeRule));
    }

    public function testValidateNoLog()
    {
        $badgeRule = new BadgeRule();

        $associatedLogs = [];
        $occurenceConstraint = new OccurenceConstraint();
        $occurenceConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertFalse($occurenceConstraint->validate());
    }

    public function testValidateNotEnoughOccurence()
    {
        $badgeRule = new BadgeRule();
        $badgeRule->setOccurrence(rand(2, PHP_INT_MAX));

        $associatedLogs = [new Log()];
        $occurenceConstraint = new OccurenceConstraint();
        $occurenceConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertFalse($occurenceConstraint->validate());
    }

    public function testValidateEnoughOccurence()
    {
        $badgeRule = new BadgeRule();
        $badgeRule->setOccurrence(2);

        $associatedLogs = [new Log(), new Log(), new Log()];
        $occurenceConstraint = new OccurenceConstraint();
        $occurenceConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($occurenceConstraint->validate());
    }
}
