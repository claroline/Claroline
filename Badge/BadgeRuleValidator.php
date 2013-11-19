<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Badge;

use Claroline\CoreBundle\Badge\Constraints\OccurenceConstraint;
use Claroline\CoreBundle\Badge\Constraints\ResourceConstraint;
use Claroline\CoreBundle\Badge\Constraints\ResultConstraint;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\Log\LogRepository;

class BadgeRuleValidator
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
     * @param Badge $badge
     * @param User  $user
     *
     * @return array
     */
    public function validateBadge(Badge $badge, User $user)
    {
        $return    = array();
        $isChecked = true;

        $badgeRules = $badge->getBadgeRules();

        if (0 < count($badgeRules)) {
            foreach ($badgeRules as $badgeRule) {

                $checkedLogs = $this->validateRule($badge->getWorkspace(), $badgeRule, $user);

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
     * @param BadgeRule              $badgeRule
     * @param User                   $user
     *
     * @return bool|Log[]
     */
    public function validateRule($workspace, BadgeRule $badgeRule, User $user)
    {
        /** @var \Claroline\CoreBundle\Entity\Log\Log[] $associatedLogs */
        $associatedLogs = $this->logRepository->findByWorkspaceBadgeRuleAndUser($workspace, $badgeRule, $user);

        $isValid     = true;
        $constraints = array();

        $occurenceConstraint = new OccurenceConstraint($badgeRule, $associatedLogs);

        if ($occurenceConstraint->validate()) {
            $badgeRuleResult = $badgeRule->getResult();
            if (null !== $badgeRuleResult) {
                $constraints[] = new ResultConstraint($badgeRule, $associatedLogs);
            }

            $badgeRuleResource = $badgeRule->getResource();
            if (null !== $badgeRuleResource) {
                $constraints[] = new ResourceConstraint($badgeRule, $associatedLogs);
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
