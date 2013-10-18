<?php

namespace Claroline\CoreBundle\Badge;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Repository\Log\LogRepository;

class BadgeRuleChecker
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
    public function checkBadge(Badge $badge, User $user)
    {
        $return    = array();
        $isChecked = true;

        $badgeRules = $badge->getBadgeRules();

        if (0 < count($badgeRules)) {
            foreach ($badgeRules as $badgeRule) {

                $checkedLogs = $this->checkRule($badge->getWorkspace(), $badgeRule, $user);

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
    public function checkRule($workspace, BadgeRule $badgeRule, User $user)
    {
        /** @var \Claroline\CoreBundle\Entity\Log\Log[] $associatedLogs */
        $associatedLogs = $this->logRepository->findByWorkspaceBadgeRuleAndUser($workspace, $badgeRule, $user);

        $checkRule = false;

        if (0 < count($associatedLogs) && count($associatedLogs) >= $badgeRule->getOccurrence()) {
            $badgeRuleResult = $badgeRule->getResult();
            if (null !== $badgeRuleResult) {
                $badgeRuleResultComparison = $badgeRule->getResultComparison();
                $resultCOmparisonTypes     = BadgeRule::getResultComparisonTypes();
                foreach ($associatedLogs as $associatedLog) {
                    $associatedLogDetails = $associatedLog->getDetails();
                    if (isset($associatedLogDetails['result']) && $this->compareResult($associatedLogDetails['result'], $badgeRuleResult, $resultCOmparisonTypes[$badgeRuleResultComparison])) {
                        $checkRule = $associatedLogs;
                    }
                }
            }
            else {
                $checkRule = $associatedLogs;
            }
        }

        return $checkRule;
    }

    /**
     * @param string $value
     * @param string $comparedValue
     * @param string $comparisonType
     *
     * @return bool
     */
    protected function compareResult($value, $comparedValue, $comparisonType)
    {
        return version_compare($value, $comparedValue, $comparisonType);
    }
}
