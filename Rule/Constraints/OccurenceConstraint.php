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

use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Entity\Log\Log;

class OccurenceConstraint extends AbstractConstraint
{
    /**
     * @return bool
     */
    public function validate()
    {
        $isValid               = false;
        $countedAssociatedLogs = count($this->getAssociatedLogs());

        if (0 < $countedAssociatedLogs && $countedAssociatedLogs >= $this->getRule()->getOccurrence()) {
            $isValid = true;
        }

        return $isValid;
    }
}
