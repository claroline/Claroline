<?php

namespace Claroline\CoreBundle\Library\Security\RightManager\Delegate;

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\SecurityException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.security.right_manager.delegate.user")
 */
class UserDelegate implements SubjectDelegateInterface
{
    /** @var EntityManager */
    private $em;

    /** @var UserRepository */
    private $userRepository;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->userRepository = $this->em->getRepository('ClarolineCoreBundle:User');
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
        $userName = $sid->getUsername();
        $user = $this->userRepository->findOneByUsername($userName);

        return $user;
    }
}