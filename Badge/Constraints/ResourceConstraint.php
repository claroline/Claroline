<?php

namespace Claroline\CoreBundle\Badge\Constraints;

use Claroline\CoreBundle\Entity\Badge\BadgeRule;
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
            $isValid = $isValid && ($associatedLog->getResourceNode() === $this->getBadgeRule()->getResource());
        }

        return $isValid;
    }
}
