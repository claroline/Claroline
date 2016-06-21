<?php

namespace Innova\PathBundle\Manager;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\UserProgression;

/**
 * Class UserProgressionManager.
 */
class UserProgressionManager
{
    /**
     * Object manager.
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $securityToken;

    /**
     * Class constructor.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager                                          $objectManager
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $securityToken
     */
    public function __construct(
        ObjectManager         $objectManager,
        TokenStorageInterface $securityToken)
    {
        $this->om = $objectManager;
        $this->securityToken = $securityToken;
    }

    /**
     * Create a new progression for a User and a Step (by default, the first action is 'seen').
     *
     * @param Step   $step
     * @param User   $user
     * @param string $status
     * @param bool   $authorized
     *
     * @return UserProgression
     */
    public function create(Step $step, User $user = null, $status = null, $authorized = false)
    {
        if (empty($user)) {
            // Load current logged User
            $user = $this->securityToken->getToken()->getUser();
        }

        $progression = new UserProgression();

        $progression->setUser($user);
        $progression->setStep($step);

        if (empty($status)) {
            $status = UserProgression::getDefaultStatus();
        }

        $progression->setStatus($status);
        $progression->setAuthorized($authorized);

        $this->om->persist($progression);
        $this->om->flush();

        return $progression;
    }

    public function update(Step $step, User $user = null, $status, $authorized = false)
    {
        if (empty($user)) {
            // Load current logged User
            $user = $this->securityToken->getToken()->getUser();
        }

        // Retrieve the current progression for this step
        $progression = $this->om->getRepository('InnovaPathBundle:UserProgression')->findOneBy(array(
            'step' => $step,
            'user' => $user,
        ));

        if (empty($progression)) {
            // No progression for User => initialize a new one
            $progression = $this->create($step, $user, $status, $authorized);
        } else {
            // Update existing progression
            $progression->setStatus($status);
            $progression->setAuthorized($authorized);

            $this->om->persist($progression);
            $this->om->flush();
        }

        return $progression;
    }
}
