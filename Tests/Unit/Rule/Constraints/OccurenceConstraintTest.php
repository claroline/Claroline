<?php

namespace Icap\BadgeBundle\Rule\Constraints;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Icap\BadgeBundle\Entity\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

class OccurenceConstraintTest extends MockeryTestCase
{
    public function testIsNotApplicableTo()
    {
        $badgeRule           = new BadgeRule();
        $occurenceConstraint = new ResultConstraint();

        $this->assertFalse($occurenceConstraint->isApplicableTo($badgeRule));
    }

    public function testIsApplicableTo()
    {
        $badgeRule           = new BadgeRule();
        $badgeRule->setOccurrence(rand(0, PHP_INT_MAX));

        $occurenceConstraint = new ResultConstraint();

        $this->assertFalse($occurenceConstraint->isApplicableTo($badgeRule));
    }

    public function testValidateNoLog()
    {
        $badgeRule = new BadgeRule();

        $associatedLogs      = array();
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

        $associatedLogs = array(new Log());
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

        $associatedLogs = array(new Log(), new Log(), new Log());
        $occurenceConstraint = new OccurenceConstraint();
        $occurenceConstraint
            ->setRule($badgeRule)
            ->setAssociatedLogs($associatedLogs);

        $this->assertTrue($occurenceConstraint->validate());
    }
}
