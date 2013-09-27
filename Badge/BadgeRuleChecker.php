<?php

namespace Claroline\CoreBundle\Badge;

use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\User;
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
     * @param BadgeRule $rule
     * @param User      $user
     *
     * @return bool
     */
    public function checkRule(BadgeRule $rule, User $user)
    {
        $associatedLogs = $this->logRepository->findByBadgeRuleAndUser($rule, $user);

        return (0 < count($associatedLogs)) ? $associatedLogs : false;
    }
}
