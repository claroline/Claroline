<?php

namespace Claroline\OpenBadgeBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Manager\AssertionManager;
use Claroline\OpenBadgeBundle\Manager\RuleManager;
use Claroline\OpenBadgeBundle\Messenger\Message\GrantBadge;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Searches users who meet the Badge rules and grant them the badge.
 */
class GrantBadgeHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var RuleManager */
    private $ruleManager;
    /** @var AssertionManager */
    private $assertionManager;

    public function __construct(
        ObjectManager $om,
        RuleManager $ruleManager,
        AssertionManager $assertionManager
    ) {
        $this->om = $om;
        $this->ruleManager = $ruleManager;
        $this->assertionManager = $assertionManager;
    }

    public function __invoke(GrantBadge $grantBadge)
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
