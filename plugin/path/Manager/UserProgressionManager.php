<?php

namespace Innova\PathBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Entity\UserProgression;
use Innova\PathBundle\Repository\UserProgressionRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @DI\Service("innova_path.manager.user_progression")
 */
class UserProgressionManager
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var UserProgressionRepository
     */
    private $repository;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * UserProgressionManager constructor.
     *
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     *
     * @param ObjectManager         $om
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        ObjectManager         $om,
        TokenStorageInterface $tokenStorage)
    {
        $this->om = $om;
        $this->repository = $this->om->getRepository('InnovaPathBundle:UserProgression');
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Calculates how many steps are seen or done in a path for a user,
     * a measure to estimate total user progression over the path.
     *
     * @param Path      $path
     * @param User|null $user
     *
     * @return int
     */
    public function calculateUserProgressionInPath(Path $path, User $user = null)
    {
        if (empty($user)) {
            // Load current logged User
            $user = $this->tokenStorage->getToken()->getUser();
        }

        if (!$user instanceof User) {
            return 0;
        }

        return $this->repository->countProgressionForUserInPath($path, $user);
    }

    public function calculateUserProgression(User $user, array $paths)
    {
        return $this->repository->findUserProgression($user, $paths);
    }

    /**
     * Get progression of a User into a Path.
     *
     * @param \Innova\PathBundle\Entity\Path\Path $path
     * @param \Claroline\CoreBundle\Entity\User   $user
     *
     * @return array
     */
    public function getUserProgression(Path $path, User $user = null)
    {
        if (empty($user)) {
            // Get current authenticated User
            $user = $this->tokenStorage->getToken()->getUser();
        }

        $results = [];
        if ($user instanceof UserInterface) {
            // We have a logged User => get its progression
            $results = $this->repository->findByPathAndUser($path, $user);
        }

        return $results;
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
        // Check if progression already exists, if so return retrieved progression
        if ($checkDuplicate && $user instanceof User) {
            /** @var UserProgression $progression */
            $progression = $this->om->getRepository('InnovaPathBundle:UserProgression')->findOneBy([
                'step' => $step,
                'user' => $user,
            ]);

            if (!empty($progression)) {
                return $progression;
            }
        }

        $progression = new UserProgression();

        $progression->setStep($step);

        if (empty($status)) {
            $status = UserProgression::getDefaultStatus();
        }

        $progression->setStatus($status);
        $progression->setAuthorized($authorized);

        if ($user instanceof User) {
            $progression->setUser($user);
            $this->om->persist($progression);
            $this->om->flush();
        }

        return $progression;
    }

    public function update(Step $step, User $user = null, $status, $authorized = false)
    {
        // Retrieve the current progression for this step
        $progression = $this->om->getRepository('InnovaPathBundle:UserProgression')->findOneBy([
            'step' => $step,
            'user' => $user,
        ]);

        if (empty($progression)) {
            // No progression for User => initialize a new one
            $progression = $this->create($step, $user, $status, $authorized, false);
        } else {
            // Update existing progression
            $progression->setStatus($status);
            $progression->setAuthorized($authorized);
            if ($user instanceof User) {
                $this->om->persist($progression);
                $this->om->flush();
            }
        }

        return $progression;
    }

    /**
     * Update state of the lock for User Progression for a step.
     *
     * @param User      $user
     * @param Step      $step
     * @param bool|null $lockedcall
     * @param bool|null $lock
     * @param bool|null $authorized
     *
     * @return UserProgression
     */
    public function updateLockedState(User $user, Step $step, $lockedcall = null, $lock = null, $authorized = null, $status = '')
    {
        // Retrieve the current progression for this step
        $progression = $this->om->getRepository('InnovaPathBundle:UserProgression')
            ->findOneBy([
            'step' => $step,
            'user' => $user,
        ]);
        if ($progression === null) {
            $progression = new UserProgression();
            $progression->setUser($user);
            $progression->setStep($step);
            $progression->setStatus($status);
            $progression->setAuthorized(false);
        }
        //if unlock call has changed
        if ($lockedcall !== null) {
            $progression->setLockedcall($lockedcall);
        }
        //if lock state has changed
        if ($lock !== null) {
            $progression->setLocked($lock);
        }
        //if authorization has changed
        if ($authorized !== null) {
            $progression->setAuthorized($authorized);
        }
        //if status has changed
        if ($status !== null) {
            $progression->setStatus($status);
        }
        $this->om->persist($progression);
        $this->om->flush();

        return $progression;
    }
}
