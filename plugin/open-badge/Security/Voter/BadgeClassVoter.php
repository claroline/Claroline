<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\OpenBadgeBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;

class BadgeClassVoter extends AbstractVoter
{
    public function getClass()
    {
        return BadgeClass::class;
    }
}
