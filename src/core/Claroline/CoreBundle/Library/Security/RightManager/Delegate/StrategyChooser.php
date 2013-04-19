<?php

namespace Claroline\CoreBundle\Library\Security\RightManager\Delegate;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Security\SecurityException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.security.right_manager.strategy_chooser")
 */
class StrategyChooser
{
    /** @var TargetDelegateInterface */
    private $entityDelegate;

    /** @var TargetDelegateInterface */
    private $classDelegate;

    /** @var SubjectDelegateInterface */
    private $userDelegate;

    /** @var SubjectDelegateInterface */
    private $roleDelegate;

    /**
     * @DI\InjectParams({
     *     "entityDelegate" = @DI\Inject("claroline.security.right_manager.delegate.entity"),
     *     "classDelegate" = @DI\Inject("claroline.security.right_manager.delegate.class"),
     *     "userDelegate" = @DI\Inject("claroline.security.right_manager.delegate.user"),
     *     "roleDelegate" = @DI\Inject("claroline.security.right_manager.delegate.role")
     * })
     */
    public function __construct($entityDelegate, $classDelegate, $userDelegate, $roleDelegate)
    {
        $this->entityDelegate = $entityDelegate;
        $this->classDelegate = $classDelegate;
        $this->userDelegate = $userDelegate;
        $this->roleDelegate = $roleDelegate;
    }

    public function chooseTargetStrategy($target)
    {
        if (is_null($target)) {
            return null;
        }

        if ($this->isAnEntity($target)) {
            return $this->entityDelegate;
        }

        if ($this->isAClass($target)) {
            return $this->classDelegate;
        }

        throw new SecurityException("Cannot choose Target Strategy for [{$target}]");
    }

    public function chooseSubjectStrategy($subject)
    {
        if (is_null($subject)) {
            return null;
        }

        if ($this->isAUser($subject)) {
            return $this->userDelegate;
        }

        if ($this->isARole($subject)) {
            return $this->roleDelegate;
        }

        throw new SecurityException("Cannot choose Subject Strategy for [{$subject}]");
    }

    public function getEntityDelegate()
    {
        return $this->entityDelegate;
    }

    public function getClassDelegate()
    {
        return $this->classDelegate;
    }

    public function getUserDelegate()
    {
        return $this->userDelegate;
    }

    public function getRoleDelegate()
    {
        return $this->roleDelegate;
    }

    private function isAnEntity($target)
    {
        return is_object($target);
    }

    private function isAClass($target)
    {
        return is_string($target) && class_exists($target, false);
    }

    private function isAUser($subject)
    {
        return $subject instanceof User;
    }

    private function isARole($subject)
    {
        return $subject instanceof Role;
    }
}