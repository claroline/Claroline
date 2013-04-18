<?php

namespace Claroline\CoreBundle\Library\Security\RightManager\Delegate;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Claroline\CoreBundle\Library\Security\SecurityException;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.security.right_manager.delegate.role")
 */
class RoleDelegate implements SubjectDelegateInterface
{
    /** @var EntityManager */
    private $em;

    /** @var EntityRepository */
    private $roleRepository;

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager")
     * })
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->roleRepository = $this->em->getRepository('ClarolineCoreBundle:Role');
    }

    public function buildSecurityIdentity($subject)
    {
        if ($subject->getId() === null) {
            throw new SecurityException(
                "The role must be saved before being granted any right.",
                SecurityException::INVALID_ROLE_STATE
            );
        }

        return new RoleSecurityIdentity($subject->getName());
    }

    public function buildSubject($sid)
    {
        $roleName = $sid->getRole();
        $role = $this->roleRepository->findOneByName($roleName);

        return $role;
    }
}