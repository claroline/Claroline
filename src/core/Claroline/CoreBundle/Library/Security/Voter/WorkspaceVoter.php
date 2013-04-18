<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;

class WorkspaceVoter implements VoterInterface
{
    private $em;
    private $translator;
    private $ut;

    public function __construct(EntityManager $em, Translator $translator, Utilities $ut)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->ut = $ut;
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
        $manager = $this->em->getRepository('ClarolineCoreBundle:Role')->findManagerRole($workspace);

        if ($action === 'DELETE') {
            return $token->getUser()->hasRole($manager->getName()) ? true : false;
        }

        $tools = $this->em
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findByRolesAndWorkspace($this->ut->getRoles($token), $workspace, true);

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
