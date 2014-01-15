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

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

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

        $isApplicableTo = $receiverConstraint->isApplicableTo($badgeRule);
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
        $receiverConstraint->setAssociatedLogs(array());

        $this->assertFalse($receiverConstraint->validate());
    }

    public function testValidateOneLog()
    {
        $receiverConstraint = new ReceiverConstraint();
        $receiverConstraint->setAssociatedLogs(array(new Log()));

        $this->assertTrue($receiverConstraint->validate());
    }
}
