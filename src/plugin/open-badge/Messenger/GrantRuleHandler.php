<?php

namespace Claroline\OpenBadgeBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Manager\AssertionManager;
use Claroline\OpenBadgeBundle\Manager\RuleManager;
use Claroline\OpenBadgeBundle\Messenger\Message\GrantRule;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Creates an evidence when a user meet a Badge rule and checks if the user is granted the badge.
 */
class GrantRuleHandler implements MessageHandlerInterface
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

    public function __invoke(GrantRule $grantRule)
    {
        /** @var Rule $rule */
        $rule = $this->om->getRepository(Rule::class)->findOneBy(['uuid' => $grantRule->getRuleId()]);
        /** @var User $user */
        $user = $this->om->getRepository(User::class)->findOneBy(['uuid' => $grantRule->getUserId()]);

        if (!empty($rule) && !empty($user)) {
            $this->ruleManager->createEvidence($rule, $user);

            // checks if the user can be granted the badge now
            $this->assertionManager->grant($rule->getBadge(), $user);
        }
    }
}
