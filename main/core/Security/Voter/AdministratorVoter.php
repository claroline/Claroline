<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\CoreBundle\Security\PlatformRoles;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * This voter grants access to admin users, whenever the attribute or the
 * class is. This means that administrators are seen by the AccessDecisionManager
 * as if they have all the possible roles and permissions on every object or class.
 *
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class AdministratorVoter implements VoterInterface
{
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $isImpersonating = $this->isUsurpatingWorkspaceRole($token);

        return $this->isAdmin($token) ? (VoterInterface::ACCESS_GRANTED && !$isImpersonating) : VoterInterface::ACCESS_ABSTAIN;
    }

    protected function isAdmin(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if (PlatformRoles::ADMIN === $role->getRole()) {
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

    private function isUsurpatingWorkspaceRole(TokenInterface $token)
    {
        foreach ($token->getRoles() as $role) {
            if ($role->getRole() === 'ROLE_USURPATE_WORKSPACE_ROLE') {
                return true;
            }
        }

        return false;
    }
}
