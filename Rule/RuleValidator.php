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
use Claroline\CoreBundle\Entity\Rule\Rule;
use Claroline\CoreBundle\Rule\Rulable;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\Log\LogRepository;

class RuleValidator
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
     * @return array
     */
    public function validate(Rulable $rulable, User $user)
    {
        $return    = array();
        $isChecked = true;

        $badgeRules = $rulable->getRules();

        if (0 < count($badgeRules)) {
            foreach ($badgeRules as $badgeRule) {

                $checkedLogs = $this->validateRule($rulable->getWorkspace(), $badgeRule, $user);

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
     * @param AbstractWorkspace|null $workspace
     * @param Rule                   $rule
     * @param User                   $user
     *
     * @return bool|Log[]
     */
    public function validateRule($workspace, Rule $rule, User $user)
    {
        /** @var \Claroline\CoreBundle\Entity\Log\Log[] $associatedLogs */
        $associatedLogs = $this->logRepository->findByWorkspaceBadgeRuleAndUser($workspace, $rule, $user);

        $isValid     = true;
        $constraints = array();

        $occurenceConstraint = new OccurenceConstraint($rule, $associatedLogs);

        if ($occurenceConstraint->validate()) {
            $ruleResult = $rule->getResult();
            if (null !== $ruleResult) {
                $constraints[] = new ResultConstraint($rule, $associatedLogs);
            }

            $ruleResource = $rule->getResource();
            if (null !== $ruleResource) {
                $constraints[] = new ResourceConstraint($rule, $associatedLogs);
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
