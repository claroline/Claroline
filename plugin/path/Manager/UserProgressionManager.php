<?php

namespace Innova\PathBundle\Manager;

use Innova\PathBundle\Entity\Path\Path;
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
     * Calculates how many steps are seen or done in a path for a user,
     * a measure to estimate total user progression over the path.
     *
     * @param Path      $path
     * @param User|null $user
     *
     * @return int $totalProgression
     */
    public function calculateUserProgressionInPath(Path $path, User $user = null)
    {
        if (empty($user)) {
            // Load current logged User
            $user = $this->securityToken->getToken()->getUser();
        }

        return $this
            ->om
            ->getRepository('InnovaPathBundle:UserProgression')
            ->countProgressionForUserInPath($path, $user);
    }

    /**
     * Create a new progression for a User and a Step (by default, the first action is 'seen').
     *
     * @param Step   $step
     * @param User   $user
     * @param string $status
     * @param bool   $authorized
     * @param bool   $checkDuplicate
     *
     * @return UserProgression
     */
    public function create(Step $step, User $user = null, $status = null, $authorized = false, $checkDuplicate = true)
    {
        if (empty($user)) {
            // Load current logged User
            $user = $this->securityToken->getToken()->getUser();
        }

        // Check if progression already exists, if so return retrieved progression
        if ($checkDuplicate) {
            $progression = $this->om->getRepository('InnovaPathBundle:UserProgression')->findOneBy(array(
                'step' => $step,
                'user' => $user,
            ));

            if (!empty($progression)) {
                return $progression;
            }
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
            $progression = $this->create($step, $user, $status, $authorized, false);
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
