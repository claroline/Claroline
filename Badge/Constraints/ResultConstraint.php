<?php

namespace Claroline\CoreBundle\Badge\Constraints;

use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;

class ResultConstraint extends AbstractConstraint
{
    /**
     * @return bool
     */
    public function validate()
    {
        $isValid               = true;
        $resultComparisonTypes = BadgeRule::getResultComparisonTypes();

        if (0 === count($this->getAssociatedLogs())) {
            $isValid = false;
        }

        foreach ($this->getAssociatedLogs() as $associatedLog) {
            $associatedLogDetails = $associatedLog->getDetails();

            if (isset($associatedLogDetails['result'])) {
                $isValid = $isValid && version_compare($associatedLogDetails['result'], $this->getBadgeRule()->getResult(), $resultComparisonTypes[$this->getBadgeRule()->getResultComparison()]);
            }
        }

        return $isValid;
    }
}
