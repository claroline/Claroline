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

class ResultConstraint extends AbstractConstraint
{
    /**
     * @return bool
     */
    public function validate()
    {
        $isValid               = true;
        $resultComparisonTypes = Rule::getResultComparisonTypes();

        if (0 === count($this->getAssociatedLogs())) {
            $isValid = false;
        }

        foreach ($this->getAssociatedLogs() as $associatedLog) {
            $associatedLogDetails = $associatedLog->getDetails();

            if (isset($associatedLogDetails['result'])) {
                $isValid = $isValid && version_compare($associatedLogDetails['result'], $this->getRule()->getResult(), $resultComparisonTypes[$this->getRule()->getResultComparison()]);
            }
        }

        return $isValid;
    }
}
