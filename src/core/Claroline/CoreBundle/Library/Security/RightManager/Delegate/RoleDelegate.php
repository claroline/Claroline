<?php

namespace Claroline\CoreBundle\Library\Security\RightManager\Delegate;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Claroline\CoreBundle\Library\Security\SecurityException;
use Claroline\CoreBundle\Manager\RoleManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.security.right_manager.delegate.role")
 */
class RoleDelegate implements SubjectDelegateInterface
{
    /** @var EntityManager */
    private $em;
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "em"             = @DI\Inject("doctrine.orm.entity_manager"),
     *     "roleManager"    = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(EntityManager $em, RoleManager $roleManager)
    {
        $this->em = $em;
        $this->roleManager = $roleManager;
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
        $role = $this->roleManager->getRoleByName($roleName);

        return $role;
    }
}