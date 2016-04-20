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

use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

class ResourceConstraintTest extends MockeryTestCase
{
    public function testIsNotApplicableTo()
    {
        $badgeRule = new BadgeRule();
        $resourceConstraint = new ResourceConstraint();

        $this->assertFalse($resourceConstraint->isApplicableTo($badgeRule));
    }

    public function testIsApplicableTo()
    {
        $badgeRule = new BadgeRule();
        $badgeRule->setResource(new Text());

        $resourceConstraint = new ResourceConstraint();

        $this->assertTrue($resourceConstraint->isApplicableTo($badgeRule));
    }

    public function testValidateNoLog()
    {
        $resourceConstraint = new ResourceConstraint();
        $resourceConstraint->setAssociatedLogs(array());

        $this->assertFalse($resourceConstraint->validate());
    }

    public function testValidateOneLog()
    {
        $resourceConstraint = new ResourceConstraint();
        $resourceConstraint->setAssociatedLogs(array(new Log()));

        $this->assertTrue($resourceConstraint->validate());
    }
}
