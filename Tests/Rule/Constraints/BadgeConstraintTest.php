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
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;
use \Mockery as m;

class BadgeConstraintTest extends MockeryTestCase
{
    public function testIsNotApplicableTo()
    {
        $badgeRule       = new BadgeRule();
        $badgeConstraint = new BadgeConstraint();

        $this->assertFalse($badgeConstraint->isApplicableTo($badgeRule));
    }

    public function testIsApplicableTo()
    {
        $badgeRule = new BadgeRule();
        $badgeRule->setBadge(new Badge());

        $badgeConstraint = new BadgeConstraint();

        $this->assertTrue($badgeConstraint->isApplicableTo($badgeRule));
    }

    public function testValidateNoLog()
    {
        $badgeConstraint = new BadgeConstraint();
        $badgeConstraint->setAssociatedLogs(array());

        $this->assertFalse($badgeConstraint->validate());
    }

    public function testValidateOneLog()
    {
        $badgeConstraint = new BadgeConstraint();
        $badgeConstraint->setAssociatedLogs(array(new Log()));

        $this->assertTrue($badgeConstraint->validate());
    }
}
