<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class WorkspaceVoter implements VoterInterface
{
    private $em;
    private $translator;
    private $ut;
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "em"             = @DI\Inject("doctrine.orm.entity_manager"),
     *     "translator"     = @DI\Inject("translator"),
     *     "ut"             = @DI\Inject("claroline.security.utilities"),
     *     "roleManager"    = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        EntityManager $em,
        Translator $translator,
        Utilities $ut,
        RoleManager $roleManager
    )
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->ut = $ut;
        $this->roleManager = $roleManager;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof AbstractWorkspace) {
            return ($this->canDo($object, $token, $attributes[0])) ?
                VoterInterface::ACCESS_GRANTED:
                VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }

    /**
     * Checks if the current token has the right to do the action $action.
     *
     * @param AbstractWorkspace $workspace
     * @param TokenInterface $token
     * @param string $action
     *
     * @return boolean
     *
     * @throws \RuntimeException
     */
    private function canDo(AbstractWorkspace $workspace, TokenInterface $token, $action)
    {
        $manager = $this->roleManager->getManagerRole($workspace);
        $roles = $this->ut->getRoles($token);

        if ($action === 'DELETE') {
            foreach ($roles as $role) {
                if ($role === $manager->getName()) {
                    return true;
                }
            }

            return false;
        }

        $tools = $this->em
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findDisplayedByRolesAndWorkspace($this->ut->getRoles($token), $workspace);

        if ($action === 'OPEN') {
            if (count($tools) > 0) {
                return true;
            } else {
                return false;
            }
        }

        foreach ($tools as $tool) {
            if ($tool->getName() === $action) {
                return true;
            }
        }

        if ($token instanceof AnonymousToken) {
            throw new AuthenticationException('Insufficient permissions : authentication required');
        }

        return false;
    }
}
