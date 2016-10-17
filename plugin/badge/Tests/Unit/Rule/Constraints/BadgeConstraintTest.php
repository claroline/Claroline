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
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Icap\BadgeBundle\Entity\Badge;
use Icap\BadgeBundle\Entity\BadgeRule;

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
        $badgeConstraint->setAssociatedLogs([]);

        $this->assertFalse($badgeConstraint->validate());
    }

    public function testValidateOneLog()
    {
        $badge = new Badge();
        $badge->setId(rand(0, PHP_INT_MAX));

        $rule = new BadgeRule();
        $rule->setBadge($badge);

        $log = new Log();
        $log->setDetails([
            'badge' => [
                'id' => $badge->getId(),
            ],
        ]);
        $badgeConstraint = new BadgeConstraint();
        $badgeConstraint
            ->setRule($rule)
            ->setAssociatedLogs([$log]);

        $this->assertTrue($badgeConstraint->validate());
    }

    public function testValidateNoLogWrongBadge()
    {
        $badge = new Badge();
        $badge->setId(rand(PHP_INT_MAX / 2 + 1, PHP_INT_MAX));

        $rule = new BadgeRule();
        $rule->setBadge($badge);

        $log = new Log();
        $log->setDetails([
            'badge' => [
                'id' => rand(0, PHP_INT_MAX / 2),
            ],
        ]);
        $badgeConstraint = new BadgeConstraint();
        $badgeConstraint
            ->setRule($rule)
            ->setAssociatedLogs([$log]);

        $this->assertFalse($badgeConstraint->validate());
    }
}
