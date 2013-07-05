<?php

namespace Claroline\CoreBundle\Library\Security\RightManager\Delegate;

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\SecurityException;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.security.right_manager.delegate.user")
 */
class UserDelegate implements SubjectDelegateInterface
{
    /** @var EntityManager */
    private $em;
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "em"             = @DI\Inject("doctrine.orm.entity_manager"),
     *     "userManager"    = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(EntityManager $em, UserManager $userManager)
    {
        $this->em = $em;
        $this->userManager = $userManager;
    }

    public function buildSecurityIdentity($subject)
    {
        if ($subject->getId() == 0) {
            throw new SecurityException(
                "The user must be saved before being granted any right.",
                SecurityException::INVALID_USER_STATE
            );
        }

        return UserSecurityIdentity::fromAccount($subject);
    }

    public function buildSubject($sid)
    {
        $username = $sid->getUsername();
        $user = $this->userManager->getUserByUsername($username);

        return $user;
    }
}