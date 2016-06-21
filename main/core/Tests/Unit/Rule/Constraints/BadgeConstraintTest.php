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
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

class BadgeConstraintTest extends MockeryTestCase
{
    public function testIsNotApplicableTo()
    {
        $badgeRule = new BadgeRule();
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
        $badge = new Badge();
        $badge->setId(rand(0, PHP_INT_MAX));

        $rule = new BadgeRule();
        $rule->setBadge($badge);

        $log = new Log();
        $log->setDetails(array(
            'badge' => array(
                'id' => $badge->getId(),
            ),
        ));
        $badgeConstraint = new BadgeConstraint();
        $badgeConstraint
            ->setRule($rule)
            ->setAssociatedLogs(array($log));

        $this->assertTrue($badgeConstraint->validate());
    }

    public function testValidateNoLogWrongBadge()
    {
        $badge = new Badge();
        $badge->setId(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX));

        $rule = new BadgeRule();
        $rule->setBadge($badge);

        $log = new Log();
        $log->setDetails(array(
            'badge' => array(
                'id' => rand(0, PHP_INT_MAX / 2),
            ),
        ));
        $badgeConstraint = new BadgeConstraint();
        $badgeConstraint
            ->setRule($rule)
            ->setAssociatedLogs(array($log));

        $this->assertFalse($badgeConstraint->validate());
    }
}
