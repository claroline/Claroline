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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Rule\Constraints\ReceiverConstraint;
use Icap\BadgeBundle\Entity\BadgeRule;

class ReceiverConstraintTest extends MockeryTestCase
{
    public function testIsNotApplicableToWrongUserType()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setUser(new User())
            ->setUserType(0);

        $receiverConstraint = new ReceiverConstraint();

        $this->assertFalse($receiverConstraint->isApplicableTo($badgeRule));
    }
    public function testIsApplicableToNoUser()
    {
        $this->setExpectedException('RuntimeException');

        $badgeRule = new BadgeRule();
        $badgeRule->setUserType(0);

        $receiverConstraint = new ReceiverConstraint();

        $receiverConstraint->isApplicableTo($badgeRule);
    }

    public function testIsApplicableTo()
    {
        $badgeRule = new BadgeRule();
        $badgeRule
            ->setUserType(1)
            ->setUser(new User());

        $receiverConstraint = new ReceiverConstraint();

        $this->assertTrue($receiverConstraint->isApplicableTo($badgeRule));
    }

    public function testValidateNoLog()
    {
        $receiverConstraint = new ReceiverConstraint();
        $receiverConstraint->setAssociatedLogs([]);

        $this->assertFalse($receiverConstraint->validate());
    }

    public function testValidateOneLog()
    {
        $receiverConstraint = new ReceiverConstraint();
        $receiverConstraint->setAssociatedLogs([new Log()]);

        $this->assertTrue($receiverConstraint->validate());
    }
}
