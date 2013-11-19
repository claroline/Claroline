<?php

namespace Claroline\CoreBundle\Badge\Constraints;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

class OccurenceConstraintTest extends MockeryTestCase
{
    public function testValidateNoLog()
    {
        $badgeRule = new BadgeRule();

        $associatedLogs = array();
        $occurenceConstraint = new OccurenceConstraint($badgeRule, $associatedLogs);

        $this->assertFalse($occurenceConstraint->validate());
    }

    public function testValidateNotEnoughOccurence()
    {
        $badgeRule = new BadgeRule();
        $badgeRule->setOccurrence(rand(2, PHP_INT_MAX));

        $associatedLogs = array(new Log());
        $occurenceConstraint = new OccurenceConstraint($badgeRule, $associatedLogs);

        $this->assertFalse($occurenceConstraint->validate());
    }

    public function testValidateEnoughOccurence()
    {
        $badgeRule = new BadgeRule();
        $badgeRule->setOccurrence(2);

        $associatedLogs = array(new Log(), new Log(), new Log());
        $occurenceConstraint = new OccurenceConstraint($badgeRule, $associatedLogs);

        $this->assertTrue($occurenceConstraint->validate());
    }
}
