<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Security\Utilities;
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

    /**
     * @DI\InjectParams({
     *     "em"             = @DI\Inject("doctrine.orm.entity_manager"),
     *     "translator"     = @DI\Inject("translator"),
     *     "ut"             = @DI\Inject("claroline.security.utilities")
     * })
     */
    public function __construct(
        EntityManager $em,
        Translator $translator,
        Utilities $ut
    )
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->ut = $ut;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof AbstractWorkspace) {

            //Managers can do anything in their workspace.
            $manager = $this->em->getRepository('ClarolineCoreBundle:Role')
                ->findManagerRole($object);
            $roles = $this->ut->getRoles($token);

            foreach ($roles as $role) {
                if ($role === $manager->getName()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }

            if (count($attributes) === 0) {
                $roles = $this->ut->getRoles($token);
                $ws = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
                    ->findWorkspaceByWorkspaceAndRoles($object, $roles);

                return (count($ws) === 0) ? VoterInterface::ACCESS_DENIED: VoterInterface::ACCESS_GRANTED;
            }

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
     * @param TokenInterface    $token
     * @param string            $action
     *
     * @return boolean
     *
     * @throws \RuntimeException
     */
    private function canDo(AbstractWorkspace $workspace, TokenInterface $token, $action)
    {
        //If a the user role is linked to a tool, he can open the workspace.
        //It may change in the future.
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

        //what is that ?
        foreach ($tools as $tool) {
            if ($tool->getName() === $action) {
                return true;
            }
        }

        //Authentication may be needed
        if ($token instanceof AnonymousToken) {
            throw new AuthenticationException('Insufficient permissions : authentication required');
        }

        return false;
    }
}
