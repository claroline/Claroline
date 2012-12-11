<?php

namespace Claroline\CoreBundle\Library\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * This voter is involved in access decisions for AbstractResource instances.
 */
class ResourceVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object instanceof AbstractResource) {
            $allowedRoles = array(
                $object->getWorkspace()->getVisitorRole()->getRole(),
                $object->getWorkspace()->getManagerRole()->getRole(),
                $object->getWorkspace()->getCollaboratorRole()->getRole()
            );

            foreach ($token->getRoles() as $role) {
                if (in_array($role->getRole(), $allowedRoles)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
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
}