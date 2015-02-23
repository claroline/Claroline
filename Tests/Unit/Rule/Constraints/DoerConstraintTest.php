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
