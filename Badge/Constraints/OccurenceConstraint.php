<?php

namespace Claroline\CoreBundle\Badge\Constraints;

use Claroline\CoreBundle\Entity\Badge\BadgeRule;
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

        if (0 < $countedAssociatedLogs && $countedAssociatedLogs >= $this->getBadgeRule()->getOccurrence()) {
            $isValid = true;
        }

        return $isValid;
    }
}
