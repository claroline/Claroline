<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Badge\UserBadge;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogBadgeAwardEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @DI\Service("claroline.manager.badge")
 */
class BadgeManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "entityManager"   = @DI\Inject("doctrine.orm.entity_manager"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(EntityManager $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager   = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Badge  $badge
     * @param User[] $users
     *
     * @return int
     */
    public function addBadgeToUsers(Badge $badge, $users)
    {
        $addedBadge = 0;

        foreach ($users as $user) {
            if ($this->addBadgeToUser($badge, $user)) {
                $addedBadge++;
            }
        }

        return $addedBadge;
    }

    /**
     * @param Badge $badge
     * @param User  $user
     *
     * @throws \Exception
     * @return bool
     */
    public function addBadgeToUser(Badge $badge, User $user)
    {
        $badgeAwarded = false;

        /** @var \Claroline\CoreBundle\Repository\Badge\BadgeRepository $badgeRepository */
        $badgeRepository = $this->entityManager->getRepository('ClarolineCoreBundle:Badge\Badge');
        $userBadge       = $badgeRepository->findUserBadge($badge, $user);

        if (null === $userBadge) {
            try {
                $userBadge = new UserBadge();
                $userBadge
                    ->setBadge($badge)
                    ->setUser($user);

                if ($badge->isExpiring()) {
                    $userBadge->setExpiredAt($this->generateExpireDate($badge));
                }

                $badge->addUserBadge($userBadge);

                $badgeAwarded = true;

                $this->entityManager->persist($badge);
                $this->entityManager->flush();

                $this->dispatchBadgeAwardingEvent($badge, $user);
            } catch(\Exception $exception) {
                throw $exception;
            }
        }

        return $badgeAwarded;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Badge\Badge $badge
     * @param \Claroline\CoreBundle\Entity\User        $user
     *
     * @return Controller
     */
    protected function dispatchBadgeAwardingEvent(Badge $badge, User $user)
    {
        $event = new LogBadgeAwardEvent($badge, $user);

        $this->dispatch($event);
    }

    /**
     * @param LogGenericEvent $event
     */
    protected function dispatch(LogGenericEvent $event)
    {
        $this->eventDispatcher->dispatch('log', $event);
    }

    /**
     * @param Badge          $badge
     * @param \DateTime|null $currentDate
     *
     * @return \DateTime
     */
    public function generateExpireDate(Badge $badge, \DateTime $currentDate = null)
    {
        if (null === $currentDate) {
            $currentDate = new \DateTime();
        }

        $modifier = sprintf("+%d %s", $badge->getExpireDuration(), $badge->getExpirePeriodTypeLabel($badge->getExpirePeriod()));
        return $currentDate->modify($modifier);
    }

    /**
     * @param BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection $newRules
     * @param BadgeRule[]|\Doctrine\Common\Collections\ArrayCollection $originalRules
     *
     * @return bool
     */
    public function isRuleChanged($newRules, $originalRules)
    {
        $isRulesChanged = false;
        $unitOfWork = $this->entityManager->getUnitOfWork();
        $unitOfWork->computeChangeSets();

        foreach ($newRules as $newRule) {
            // Check if there are new rules
            if (null === $newRule->getId()) {
                $isRulesChanged = true;
            }
            else {
                // Check if existed rules have been changed
                $changeSet = $unitOfWork->getEntityChangeSet($newRule);
                if (0 < count($changeSet)) {
                    $isRulesChanged = true;
                }
                // Remove rule from original if they were not deleted
                if ($originalRules->contains($newRule)) {
                    $originalRules->removeElement($newRule);
                }
            }
        }

        // Check if they are deleted rules (those who are not in the new but in the originals)
        if (0 < count($originalRules)) {
            $isRulesChanged = true;
        }

        return $isRulesChanged;
    }
}
