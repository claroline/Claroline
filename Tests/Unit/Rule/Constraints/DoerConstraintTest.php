<?php

namespace Icap\BadgeBundle\Rule\Constraints;

use Icap\BadgeBundle\Entity\Badge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Icap\BadgeBundle\Entity\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

class DoerConstraintTest extends MockeryTestCase
{
    public function testIsNotApplicableToWrongUserType()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setUser(new User())
            ->setUserType(1);

        $doerConstraint = new DoerConstraint();

        $this->assertFalse($doerConstraint->isApplicableTo($badgeRule));
    }
    public function testIsApplicableToNoUser()
    {
        $this->setExpectedException('RuntimeException');

        $badgeRule = new BadgeRule();
        $badgeRule->setUserType(1);

        $doerConstraint = new DoerConstraint();

        $isApplicableTo = $doerConstraint->isApplicableTo($badgeRule);
    }

    public function testIsApplicableTo()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setUserType(0)
            ->setUser(new User());

        $doerConstraint = new DoerConstraint();

        $this->assertTrue($doerConstraint->isApplicableTo($badgeRule));
    }

    public function testValidateNoLog()
    {
        $doerConstraint = new DoerConstraint();
        $doerConstraint->setAssociatedLogs(array());

        $this->assertFalse($doerConstraint->validate());
    }

    public function testValidateOneLog()
    {
        $doerConstraint = new DoerConstraint();
        $doerConstraint->setAssociatedLogs(array(new Log()));

        $this->assertTrue($doerConstraint->validate());
    }
}
