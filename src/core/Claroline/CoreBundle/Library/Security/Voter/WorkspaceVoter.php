<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\User;

class WorkspaceVoter implements VoterInterface
{
    private $em;
    private $translator;
    private $validAttributes;


    public function __construct(EntityManager $em, Translator $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
        $this->validAttributes = array('VIEW', 'EDIT', 'DELETE', 'MANAGE');
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!in_array($attributes[0], $this->validAttributes)){
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if ($object instanceof AbstractWorkspace){
            return ($this->canDo($object, $token, $attributes[0])) ? VoterInterface::ACCESS_GRANTED: VoterInterface::ACCESS_DENIED;
        }
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
        //?
        $user = new User();
        if ($token->getUser() instanceof $user){
            $rights = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceRights')->getRights($token->getUser(), $workspace);
        } else {
            $rights = $this->em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceRights')->getAnonymousRights($workspace);
        }

        $permission = 'can'.ucfirst(strtolower($action));

        return $rights[$permission];
    }
}
