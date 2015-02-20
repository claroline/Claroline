<?php

namespace Icap\BadgeBundle\Rule\Constraints;

use Icap\BadgeBundle\Entity\Badge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Icap\BadgeBundle\Entity\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

class ActionConstraintTest extends MockeryTestCase
{
    public function testIsNotApplicableNoAction()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setUser(new User());

        $actionConstraint = new ActionConstraint();

        $this->assertFalse($actionConstraint->isApplicableTo($badgeRule));
    }

    public function testIsApplicableTo()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setUser(new User())
            ->setAction(uniqid());

        $actionConstraint = new ActionConstraint();

        $this->assertTrue($actionConstraint->isApplicableTo($badgeRule));
    }

    public function testValidateNoLog()
    {
        $actionConstraint = new ActionConstraint();
        $actionConstraint->setAssociatedLogs(array());

        $this->assertFalse($actionConstraint->validate());
    }

    public function testValidateOneLog()
    {
        $actionConstraint = new ActionConstraint();
        $actionConstraint->setAssociatedLogs(array(new Log()));

        $this->assertTrue($actionConstraint->validate());
    }
}
