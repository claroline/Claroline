<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\UserProgression;

/**
 * Class UserProgressionManager
 */
class UserProgressionManager
{
    /**
     * Object manager
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * Class constructor
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->om = $objectManager;
    }

    /**
     * Create a new progression for a User and a Step (by default, the first action is 'seen')
     * @param User $user
     * @param Step $step
     * @param string $status
     * @return \Innova\PathBundle\Entity\UserProgression
     */
    public function create(User $user, Step $step, $status = null)
    {
        $progression = new UserProgression();

        $progression->setUser($user);
        $progression->setStep($step);

        if (empty($status)) {
            $status = UserProgression::getDefaultStatus();
        }

        $progression->setStatus($status);

        $this->om->persist($progression);
        $this->om->flush();

        return $progression;
    }
}
