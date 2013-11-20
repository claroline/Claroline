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

use Claroline\CoreBundle\Entity\Rule\Rule;
use Claroline\CoreBundle\Entity\Log\Log;

class ResourceConstraint extends AbstractConstraint
{
    /**
     * @return bool
     */
    public function validate()
    {
        $isValid        = true;
        $associatedLogs = $this->getAssociatedLogs();

        if (0 === count($associatedLogs)) {
            $isValid = false;
        }

        foreach ($associatedLogs as $associatedLog) {
            $isValid = $isValid && ($associatedLog->getResourceNode() === $this->getRule()->getResource());
        }

        return $isValid;
    }
}
