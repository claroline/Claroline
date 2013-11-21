<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Rule;

use Claroline\CoreBundle\Rule\Constraints\OccurenceConstraint;
use Claroline\CoreBundle\Rule\Constraints\ResourceConstraint;
use Claroline\CoreBundle\Rule\Constraints\ResultConstraint;
use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Rule\Rulable;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\Log\LogRepository;

class Validator
{
    /**
     * @var LogRepository
     */
    private $logRepository;

    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * @param Rulable $rulable
     * @param User    $user
     *
     * @return bool|Log[]
     */
    public function validate(Rulable $rulable, User $user)
    {
        return $this->validateRules($rulable->getRules(), $user, $rulable->getRestriction());
    }

    protected function validateRules($rules, User $user, array $restriction)
    {
        $return    = array();
        $isChecked = true;

        if (0 < count($rules)) {
            foreach ($rules as $rule) {

                $checkedLogs = $this->validateRule($rule, $user, $restriction);

                if (false === $checkedLogs) {
                    $isChecked = false;
                }
                else {
                    foreach ($checkedLogs as $checkedLog) {
                        $return[] = $checkedLog;
                    }
                }
            }
        }
        else {
            $isChecked = false;
        }

        return (false === $isChecked) ? false : $return;
    }

    /**
     * @param \Claroline\CoreBundle\Rule\Entity\Rule $rule
     * @param \Claroline\CoreBundle\Entity\User      $user
     * @param array                                  $restriction
     *
     * @return bool|Log[]
     */
    public function validateRule(Rule $rule, User $user, array $restriction = array())
    {
        /** @var \Claroline\CoreBundle\Entity\Log\Log[] $associatedLogs */
        $associatedLogs      = $queryBuilder = $this->logRepository->findByRuleAndUser($rule, $user, $restriction);
        $isValid             = true;
        $constraints         = array();
        $occurenceConstraint = new OccurenceConstraint($rule, $associatedLogs);

        if ($occurenceConstraint->validate()) {
            $ruleResult = $rule->getResult();
            if (null !== $ruleResult) {
                $constraints[] = new ResultConstraint($rule, $associatedLogs);
            }

            foreach ($constraints as $constraint) {
                $isValid = $isValid && $constraint->validate();
            }
        }
        else {
            $isValid = false;
        }

        return ($isValid) ? $associatedLogs : $isValid;
    }
}
