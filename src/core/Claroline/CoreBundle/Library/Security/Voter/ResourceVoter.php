<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Workspace\ResourceRights;
use Doctrine\ORM\EntityManager;

/**
 * This voter is involved in access decisions for AbstractResource instances.
 */
class ResourceVoter implements VoterInterface
{
    private $em;
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('ClarolineCoreBundle:Workspace\ResourceRights');
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof AbstractResource) {
            $call = "can" . ucfirst(strtolower($attributes[0]));
            $rr = new ResourceRights;
            if (method_exists($rr, $call)) {
                $rights = $this->repository->getRights($token->getUser(), $object);

                if (count($rights) == 0) {
                    return VoterInterface::ACCESS_DENIED;
                }

                if ($call === 'canCreate'){
                    return VoterInterface::ACCESS_ABSTAIN;
                } else {
                    (count($rights) == 1) ? $access = $rights[0]->$call(): $access = $rights[1]->$call();
                    return $access ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
                }
            } else {
                throw new \Exception("This permission doesn't exists");
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }
}