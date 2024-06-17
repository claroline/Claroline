<?php

namespace Claroline\OpenBadgeBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Entity\Rules\Rule;
use Claroline\OpenBadgeBundle\Library\Rules\AbstractRule;
use Claroline\OpenBadgeBundle\Messenger\Message\GrantRule;
use Symfony\Component\Messenger\MessageBusInterface;

class RuleManager
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly MessageBusInterface $messageBus,
        private readonly iterable $rules
    ) {
    }

    public function getRule(string $type): ?AbstractRule
    {
        $rules = $this->rules instanceof \Traversable ? iterator_to_array($this->rules) : $this->rules;
        if (!isset($rules[$type])) {
            throw new \RuntimeException(sprintf('No rule found for type "%s" Maybe you forgot to add the "claroline.badge.rule" tag to your finder.', $type));
        }

        return $rules[$type];
    }

    public function grant(Rule $rule, User $user): void
    {
        $this->messageBus->dispatch(new GrantRule($rule->getId(), $user->getId()));
    }

    public function grantAll(Rule $rule): array
    {
        $ruleDefinition = $this->getRule($rule->getAction());

        // find all users which met the current rule
        $users = $ruleDefinition->getQualifiedUsers($rule);

        // find users which already have evidence for the rule
        $evidences = $this->om->getRepository(Evidence::class)->findBy(['rule' => $rule]);
        $owners = array_map(function (Evidence $evidence) {
            return $evidence->getUser()->getUuid();
        }, $evidences);

        $recomputeUsers = [];
        foreach ($users as $user) {
            if ($user->isEnabled() && !$user->isRemoved() && !in_array($user->getUuid(), $owners)) {
                $this->createEvidence($rule, $user);

                $recomputeUsers[$user->getUuid()] = $user; // using uuid as key will automatically dedup the array
            }
        }

        return $recomputeUsers;
    }

    public function createEvidence(Rule $rule, User $user): Evidence
    {
        $ruleDefinition = $this->getRule($rule->getAction());

        $evidence = new Evidence();

        $evidence->setName($rule->getAction());
        $evidence->setRule($rule);
        $evidence->setUser($user);

        $evidence->setNarrative($ruleDefinition->getEvidenceMessage());

        $this->om->persist($evidence);
        $this->om->flush();

        return $evidence;
    }
}
