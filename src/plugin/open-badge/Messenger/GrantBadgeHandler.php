<?php

namespace Claroline\OpenBadgeBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Manager\AssertionManager;
use Claroline\OpenBadgeBundle\Manager\RuleManager;
use Claroline\OpenBadgeBundle\Messenger\Message\GrantBadge;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Searches users who meet the Badge rules and grant them the badge.
 */
#[AsMessageHandler]
class GrantBadgeHandler
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly RuleManager $ruleManager,
        private readonly AssertionManager $assertionManager
    ) {
    }

    public function __invoke(GrantBadge $grantBadge): void
    {
        /** @var BadgeClass $badge */
        $badge = $this->om->getRepository(BadgeClass::class)->find($grantBadge->getBadgeId());
        if ($badge) {
            $recomputeUsers = [];
            foreach ($badge->getRules() as $rule) {
                $recomputeUsers = array_merge($recomputeUsers, $this->ruleManager->grantAll($rule));
            }

            // checks if users are granted the badge
            foreach ($recomputeUsers as $recomputeUser) {
                $this->assertionManager->grant($badge, $recomputeUser);
            }
        }
    }
}
