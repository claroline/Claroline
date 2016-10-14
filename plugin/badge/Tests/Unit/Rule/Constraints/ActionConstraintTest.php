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
use Claroline\CoreBundle\Rule\Constraints\ActionConstraint;
use Icap\BadgeBundle\Entity\BadgeRule;

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
        $actionConstraint->setAssociatedLogs([]);

        $this->assertFalse($actionConstraint->validate());
    }

    public function testValidateOneLog()
    {
        $actionConstraint = new ActionConstraint();
        $actionConstraint->setAssociatedLogs([new Log()]);

        $this->assertTrue($actionConstraint->validate());
    }
}
