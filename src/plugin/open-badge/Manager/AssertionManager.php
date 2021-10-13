<?php

namespace Claroline\OpenBadgeBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\OpenBadgeBundle\Entity\Evidence;
use Claroline\OpenBadgeBundle\Event\AddBadgeEvent;
use Claroline\OpenBadgeBundle\Event\BadgeEvents;
use Claroline\OpenBadgeBundle\Event\RemoveBadgeEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AssertionManager
{
    /** @var ObjectManager */
    private $om;
    /** @var EventDispatcherInterface */
    private $dispatcher;

    public function __construct(
        ObjectManager $om,
        EventDispatcherInterface $dispatcher
    ) {
        $this->om = $om;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Checks if a User meets a Badge requirements by checking Evidence for each rule and grants him if needed.
     */
    public function grant(BadgeClass $badge, User $user): ?Assertion
    {
        $isGranted = true;
        $badgeRules = $badge->getRules();

        // check if there are evidence for each badge rule
        foreach ($badgeRules as $badgeRule) {
            $evidences = $this->om->getRepository(Evidence::class)->findBy(['user' => $user, 'rule' => $badgeRule]);
            if (0 === count($evidences)) {
                $isGranted = false;
                break; // no need to continue, user can not have the badge for now
            }
        }

        if ($isGranted) {
            // link evidences to this assertion
            $evidences = [];
            foreach ($badgeRules as $badgeRule) {
                $evidences = array_merge($evidences, $this->om->getRepository(Evidence::class)->findBy(['user' => $user, 'rule' => $badgeRule]));
            }

            return $this->create($badge, $user, $evidences);
        }

        return null;
    }

    public function create(BadgeClass $badge, User $user, array $evidences = []): Assertion
    {
        $assertion = $this->om->getRepository(Assertion::class)->findOneBy(['badge' => $badge, 'recipient' => $user]);
        if (!$assertion) {
            $assertion = new Assertion();
            $assertion->setBadge($badge);
            $assertion->setRecipient($user);
            $assertion->setImage($badge->getImage());
        }

        $assertion->setRevoked(false);

        if (!empty($evidences)) {
            foreach ($evidences as $evidence) {
                $evidence->setAssertion($assertion);
                $this->om->persist($evidence);
            }
        }

        $this->om->persist($assertion);
        $this->om->flush();

        $this->dispatcher->dispatch(new AddBadgeEvent($user, $badge), BadgeEvents::ADD_BADGE);

        return $assertion;
    }

    public function revoke(Assertion $assertion): Assertion
    {
        $assertion->setRevoked(true);

        $this->om->persist($assertion);
        $this->om->flush();

        $this->dispatcher->dispatch(new RemoveBadgeEvent($assertion->getRecipient(), $assertion->getBadge()), BadgeEvents::REMOVE_BADGE);

        return $assertion;
    }

    public function delete(Assertion $assertion)
    {
        $this->om->remove($assertion);
        $this->om->flush();

        $this->dispatcher->dispatch(new RemoveBadgeEvent($assertion->getRecipient(), $assertion->getBadge()), BadgeEvents::REMOVE_BADGE);
    }
}
