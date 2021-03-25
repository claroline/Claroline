<?php

namespace Claroline\OpenBadgeBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Entity\Evidence;

class RuleManager
{
    /** @var ObjectManager */
    private $om;

    /**
     * RuleManager constructor.
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Checks if a User meets a Badge requirements and grants him if needed.
     *
     * @return Assertion
     */
    public function verifyAssertion(User $user, BadgeClass $badge)
    {
        $isGranted = true;
        $badgeRules = $badge->getRules();

        foreach ($badgeRules as $badgeRule) {
            $evidences = $this->om->getRepository(Evidence::class)->findBy(['user' => $user, 'rule' => $badgeRule]);

            if (0 === count($evidences)) {
                $isGranted = false;
            }
        }

        if ($isGranted) {
            $assertion = $this->om->getRepository(Assertion::class)->findOneBy(['recipient' => $user, 'badge' => $badge]);
            if (empty($assertion)) {
                $assertion = new Assertion();
                $assertion->setRecipient($user);
                $assertion->setBadge($badge);
            }

            // add evidences to this assertion
            foreach ($badgeRules as $badgeRule) {
                $evidences = $this->om->getRepository(Evidence::class)->findBy(['user' => $user, 'rule' => $badgeRule]);

                foreach ($evidences as $evidence) {
                    $evidence->setAssertion($assertion);
                    $this->om->persist($evidence);
                }
            }

            $this->om->persist($assertion);
            $this->om->flush();

            return $assertion;
        }

        return null;
    }
}
