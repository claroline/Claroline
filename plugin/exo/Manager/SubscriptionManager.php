<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Subscription;

/**
 * @DI\Service("ujm.exo.subscription_manager")
 */
class SubscriptionManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @DI\InjectParams({
     *     "om"          = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * Subscribes a Use to an Exercise.
     *
     * @param Exercise $exercise
     * @param User     $user
     * @param bool     $flush
     *
     * @return SubscriptionManager
     */
    public function subscribe(Exercise $exercise, User $user, $flush = false)
    {
        $subscription = new Subscription();
        $subscription->setUser($user);
        $subscription->setExercise($exercise);
        $subscription->setAdmin(true);
        $subscription->setCreator(true);

        $this->om->persist($subscription);

        if ($flush) {
            $this->om->flush();
        }

        return $this;
    }

    /**
     * Delete all Subscriptions of an Exercise.
     *
     * @param Exercise $exercise
     * @param bool     $flush
     *
     * @return SubscriptionManager
     */
    public function deleteSubscriptions(Exercise $exercise, $flush = false)
    {
        $subscriptions = $this->om->getRepository('UJMExoBundle:Subscription')
            ->findByExercise($exercise);

        foreach ($subscriptions as $subscription) {
            $this->om->remove($subscription);
        }

        if ($flush) {
            $this->om->flush();
        }

        return $this;
    }
}
