<?php

namespace Claroline\CoreBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Claroline\CoreBundle\Security\PlatformRoles;

/**
 * This voter grants access to admin users, whenever the attribute or the 
 * class is. This means that administrators are seen by the AccessDecisionManager
 * as if they have all the possible roles and permissions on every object or class.
 */
class AdministratorVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        return $this->isAdmin($token) ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_ABSTAIN;
    }

    protected function isAdmin(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role)
        {
            if (PlatformRoles::ADMIN === $role->getRole()) 
            {
                return true;
            }
        }

        return false;
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