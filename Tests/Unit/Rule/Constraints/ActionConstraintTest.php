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
